<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Hiển thị giao diện đăng nhập Admin
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Xử lý đăng nhập Admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Danh sách tài khoản quản trị
     */
    public function index()
    {
        return view('admin.admins.index', [
            'admins' => Admin::with('roles')->latest()->get(),
            'roles'  => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    /**
     * Giao diện tạo mới quản trị viên
     */
    public function create()
    {
        return view('admin.admins.create', [
            'roles' => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    /**
     * Lưu thông tin quản trị viên mới
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['nullable', 'exists:roles,name'],
        ], [
            'email.unique' => 'Email này đã tồn tại trên hệ thống.',
            'password.min' => 'Mật khẩu phải từ 8 ký tự trở lên.',
        ]);

        $admin = Admin::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $admin->assignRole($data['role']);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', "Đã thêm tài khoản \"{$admin->name}\".");
    }

    /**
     * Cập nhật vai trò quản trị viên
     */
    public function updateRole(Request $request, Admin $admin)
    {
        $data = $request->validate(['role' => ['nullable', 'exists:roles,name']]);

        $admin->syncRoles($data['role'] ? [$data['role']] : []);

        return back()->with('success', "Đã cập nhật vai trò cho {$admin->name}.");
    }

    /**
     * Xóa tài khoản quản trị viên
     */
    public function destroy(Admin $admin)
    {
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Không thể tự xóa tài khoản của mình.');
        }

        $admin->delete();

        return back()->with('success', 'Đã xóa tài khoản.');
    }

    /**
     * Đăng xuất khỏi hệ thống Admin
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }
}