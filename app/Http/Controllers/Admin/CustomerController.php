<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->withCount('orders');

        // Tìm kiếm theo tên, email, SĐT
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo trạng thái khóa / hoạt động
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Lọc theo tổng chi tiêu tối thiểu
        if ($request->filled('min_spent')) {
            $query->where('total_spent', '>=', $request->min_spent);
        }

        // Lọc theo Hạng thành viên
        if ($request->filled('rank')) {
            match ($request->rank) {
                'diamond' => $query->where('total_spent', '>=', 10000000),
                'gold'    => $query->whereBetween('total_spent', [5000000, 9999999]),
                'silver'  => $query->whereBetween('total_spent', [2000000, 4999999]),
                'bronze'  => $query->where('total_spent', '<', 2000000),
                default   => null
            };
        }

        $customers = $query->latest('created_at')->paginate(10);
        $customers->appends($request->all()); // Giữ lại tham số bộ lọc khi chuyển trang mà không bị lỗi IDE

        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = User::withCount('orders')->findOrFail($id);

        // Nạp danh sách đơn hàng mới nhất
        if (method_exists($customer, 'orders')) {
            $customer->load(['orders' => fn($q) => $q->latest()]);
        }

        // Nạp giỏ hàng an toàn (tránh lỗi nếu bảng cart_items thiếu cột user_id)
        try {
            $customer->load(['cartItems.product']);
        } catch (\Throwable $e) {
            // Giữ giỏ hàng rỗng nếu cấu trúc bảng khác biệt
        }

        // Nạp danh sách yêu thích an toàn
        try {
            $customer->load(['wishlist.product']);
        } catch (\Throwable $e) {
            // Giữ wishlist rỗng nếu chưa kết nối thành công
        }

        return view('admin.customers.show', compact('customer'));
    }

    public function toggleStatus($id)
    {
        $customer = User::findOrFail($id);
        $customer->is_active = !($customer->is_active ?? true);
        $customer->save();

        $statusText = $customer->is_active ? 'mở khóa' : 'khóa';
        return back()->with('success', "Đã {$statusText} tài khoản thành công!");
    }
}