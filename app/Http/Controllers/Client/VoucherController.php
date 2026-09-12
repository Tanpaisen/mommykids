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

        // Truy vấn lấy các Voucher đủ điều kiện hiển thị cho khách
        $vouchers = Voucher::where('status', 'active')
            ->where('is_public', true) // Chỉ lấy mã công khai
            ->where(function ($query) use ($now) {
                // Đã đến thời gian bắt đầu (hoặc không cài đặt thời gian)
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                // Chưa hết hạn (hoặc không cài đặt hạn)
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($query) {
                // Còn lượt sử dụng (hoặc không giới hạn)
                $query->whereNull('total_quantity')
                      ->orWhere('total_quantity', '>', 0);
            })
            ->latest()
            ->get();

        // Tách ra 2 mảng nhỏ nếu Frontend cần phân loại Tab dễ hơn (Tùy chọn)
        $orderVouchers = $vouchers->where('type', 'order');
        $shippingVouchers = $vouchers->where('type', 'shipping');

        return view('client.vouchers.index', compact('vouchers', 'orderVouchers', 'shippingVouchers'));
    }

    public function saveVoucher(Request $request)
    {
        // 1. Khách vãng lai chưa đăng nhập -> Trả về lỗi 401 bắt đi đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu mã ưu đãi!'
            ], 401);
        }

        $request->validate(['code' => 'required|string']);
        $user = Auth::user();

        // 2. Tìm mã Voucher xem có hợp lệ không
        $voucher = Voucher::where('code', $request->code)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ưu đãi không tồn tại hoặc đã ngừng hoạt động!'
            ], 404);
        }

        // 3. Check xem khách đã lưu mã này chưa (Tránh spam click)
        if ($user->savedVouchers()->where('voucher_id', $voucher->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã lưu mã này trong ví rồi!'
            ], 400);
        }

        // 4. Nếu êm xuôi -> Attach (Insert 1 dòng vào bảng voucher_users)
        $user->savedVouchers()->attach($voucher->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu mã vào ví thành công!'
        ]);
    }
}