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


        /*
        |--------------------------------------------------------------------------
        | Tìm kiếm khách hàng
        |--------------------------------------------------------------------------
        | Chỉ dùng các cột đang có trong database users:
        | name, email
        |--------------------------------------------------------------------------
        */

        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");

            });

        }



        /*
        |--------------------------------------------------------------------------
        | Lọc trạng thái tài khoản
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'is_active',
                $request->status
            );

        }



        /*
        |--------------------------------------------------------------------------
        | Lọc theo mức chi tiêu
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_spent')) {

            $query->where(
                'total_spent',
                '>=',
                $request->min_spent
            );

        }



       /*
|--------------------------------------------------------------------------
| Lọc theo hạng thành viên
|--------------------------------------------------------------------------
*/

if ($request->filled('rank')) {

    $query->where('tier', $request->rank);

}



        /*
        |--------------------------------------------------------------------------
        | Lấy danh sách khách hàng
        |--------------------------------------------------------------------------
        */

        $customers = $query
            ->latest('created_at')
            ->paginate(10);



        // giữ bộ lọc khi chuyển trang

        $customers->appends(
            $request->all()
        );



        return view(
            'admin.customers.index',
            compact('customers')
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Chi tiết khách hàng
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {

        $customer = User::withCount('orders')
            ->findOrFail($id);



        // Load đơn hàng

        if (method_exists($customer, 'orders')) {

            $customer->load([
                'orders' => function($q){

                    $q->latest();

                }
            ]);

        }



        // Load giỏ hàng an toàn

        try {

            $customer->load([
                'cartItems.product'
            ]);

        } catch (\Throwable $e) {

        }



        // Load wishlist an toàn

        try {

            $customer->load([
                'wishlist.product'
            ]);

        } catch (\Throwable $e) {

        }



        return view(
            'admin.customers.show',
            compact('customer')
        );

    }




    /*
    |--------------------------------------------------------------------------
    | Khóa / mở khóa tài khoản
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id)
    {

        $customer = User::findOrFail($id);



        $customer->is_active =
            !($customer->is_active ?? true);



        $customer->save();



        $statusText =
            $customer->is_active
            ? 'mở khóa'
            : 'khóa';



        return back()->with(
            'success',
            "Đã {$statusText} tài khoản thành công!"
        );

    }
}