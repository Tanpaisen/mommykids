<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Services\CartService;

class VoucherController extends Controller
{
    public function index()
    {
        // 🔴 Lấy từ Redis, hết hạn 5 phút
        $vouchers = Cache::remember('vouchers_public', 300, function () {
            $now = now();
            return Voucher::where('status', 'active')
                ->where('is_public', true)
                ->where('require_save_to_user', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
                ->where(fn ($q) => $q->whereNull('total_quantity')->orWhere('total_quantity', '>', 0))
                ->latest()
                ->get();
        });

        // Lấy ID voucher đã lưu
        $savedVoucherIds = Auth::check()
            ? Auth::user()->savedVouchers()->pluck('vouchers.id')->toArray()
            : [];

        // Loại bỏ voucher đã lưu
        if (!empty($savedVoucherIds)) {
            $vouchers = $vouchers->whereNotIn('id', $savedVoucherIds);
        }

        // Tách loại
        $orderVouchers = $vouchers->where('type', 'order');
        $shippingVouchers = $vouchers->where('type', 'shipping');

        return view('client.vouchers.index', compact(
            'vouchers',
            'orderVouchers',
            'shippingVouchers',
            'savedVoucherIds'
        ));
    }

    public function saveVoucher(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu mã ưu đãi!'
            ], 401);
        }

        $request->validate(['id' => 'required|string']);
        $user = Auth::user();

        $voucher = Voucher::where('id', $request->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ưu đãi không tồn tại hoặc đã ngừng hoạt động!'
            ], 404);
        }

        if ($voucher->expires_at && $voucher->expires_at < now()) {
            return response()->json(['success' => false, 'message' => 'Rất tiếc, mã ưu đãi này vừa hết hạn!'], 400);
        }
        if ($voucher->total_quantity !== null && $voucher->total_quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Rất tiếc, mã ưu đãi này đã hết lượt lưu!'], 400);
        }

        if ($user->savedVouchers()->where('voucher_id', $voucher->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã lưu mã này trong ví rồi!'
            ], 400);
        }

        $user->savedVouchers()->attach($voucher->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu mã vào ví thành công!'
        ]);
    }
   public function wallet()
{
    $user = Auth::user();

    $vouchers = $user->savedVouchers()
        ->with('usages')
        ->latest('vouchers.created_at')
        ->get();

    $now = now();

    $availableVouchers = $vouchers->filter(function ($voucher) use ($user, $now) {
        if ($voucher->status !== 'active') return false;
        if ($voucher->starts_at && $voucher->starts_at->gt($now)) return false;
        if ($voucher->expires_at && $voucher->expires_at->lt($now)) return false;

        if (
            $voucher->total_quantity !== null &&
            $voucher->used_count >= $voucher->total_quantity
        ) {
            return false;
        }

        return true;
    });

    $usedVouchers = $vouchers->filter(function ($voucher) use ($user) {
        return $voucher->usages
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->isNotEmpty();
    });

    $expiredVouchers = $vouchers->filter(function ($voucher) use ($now) {
        return $voucher->expires_at &&
               $voucher->expires_at->lt($now);
    });

    return view('client.vouchers.wallet', compact(
        'vouchers',
        'availableVouchers',
        'usedVouchers',
        'expiredVouchers'
    ));
}
}