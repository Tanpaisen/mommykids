<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.admins.index', [
            'admins' => Admin::with('roles')->latest()->get(),
            'roles'  => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.admins.create', [
            'roles' => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['nullable', 'exists:roles,name'],
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

    public function updateRole(Request $request, Admin $admin)
    {
        $data = $request->validate(['role' => ['nullable', 'exists:roles,name']]);

        $admin->syncRoles($data['role'] ? [$data['role']] : []);

        return back()->with('success', "Đã cập nhật vai trò cho {$admin->name}.");
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Không thể tự xóa tài khoản của mình.');
        }

        $admin->delete();

        return back()->with('success', 'Đã xóa tài khoản.');
    }
}