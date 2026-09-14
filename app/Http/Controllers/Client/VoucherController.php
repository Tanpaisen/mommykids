<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $now = now();

        // Khởi tạo Query lấy các Voucher đủ điều kiện hiển thị
        $query = Voucher::where('status', 'active')
            ->where('is_public', true) // Chỉ lấy mã công khai
            ->where(function ($q) use ($now) {
                // Đã đến thời gian bắt đầu
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                // Chưa hết hạn
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($q) {
                // Còn lượt sử dụng
                $q->whereNull('total_quantity')
                  ->orWhere('total_quantity', '>', 0);
            });

        // LOẠI TRỪ CÁC VOUCHER ĐÃ LƯU (Nếu khách đã đăng nhập)
        if (Auth::check()) {
            $savedVoucherIds = Auth::user()->savedVouchers()->pluck('vouchers.id')->toArray();
            
            if (!empty($savedVoucherIds)) {
                $query->whereNotIn('id', $savedVoucherIds);
            }
        }

        // Lấy danh sách cuối cùng
        $vouchers = $query->latest()->get();

        // Tách ra 2 mảng nhỏ cho Tabs
        $orderVouchers = $vouchers->where('type', 'order');
        $shippingVouchers = $vouchers->where('type', 'shipping');

        return view('client.vouchers.index', compact('vouchers', 'orderVouchers', 'shippingVouchers'));
    }

    public function saveVoucher(Request $request)
    {
        // 1. Khách vãng lai chưa đăng nhập -> Trả về lỗi 401
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu mã ưu đãi!'
            ], 401);
        }

        // JS đang gửi id lên thay vì code
        $request->validate(['id' => 'required|string']);
        $user = Auth::user();

        // 2. Tìm mã Voucher
        $voucher = Voucher::where('id', $request->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ưu đãi không tồn tại hoặc đã ngừng hoạt động!'
            ], 404);
        }

        // Xác minh lại một lần nữa phòng trường hợp khách ngâm tab quá lâu
        if ($voucher->expires_at && $voucher->expires_at < now()) {
            return response()->json(['success' => false, 'message' => 'Rất tiếc, mã ưu đãi này vừa hết hạn!'], 400);
        }

        if ($voucher->total_quantity !== null && $voucher->total_quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Rất tiếc, mã ưu đãi này đã hết lượt lưu!'], 400);
        }

        // 3. Check xem khách đã lưu mã này chưa (Tránh spam click/request)
        if ($user->savedVouchers()->where('voucher_id', $voucher->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã lưu mã này trong ví rồi!'
            ], 400);
        }

        // 4. Nếu êm xuôi -> Attach (Insert 1 dòng vào bảng voucher_users)
        $user->savedVouchers()->attach($voucher->id);

        // (Tùy chọn) 5. Trừ đi 1 lượt sử dụng của voucher trên hệ thống
        // Nếu logic của bạn là "Cứ cất vào ví là mất 1 slot (xí chỗ)" thì bỏ comment dòng dưới:
        // if ($voucher->total_quantity !== null) {
        //     $voucher->decrement('total_quantity');
        // }

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu mã vào ví thành công!'
        ]);
    }
}