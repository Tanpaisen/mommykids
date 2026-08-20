<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function create()
    {
        return view('admin.admins.create', [
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'exists:roles,name'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $admin->assignRole($data['role']);
        }

        return redirect()->route('admin.roles.index')->with('success', "Đã thêm tài khoản “{$admin->name}”.");
    }

    /** PATCH /admin/quan-tri-vien/{admin}/vai-tro — inline role select in admin/roles/index.blade.php */
    public function updateRole(Request $request, Admin $admin)
    {
        $data = $request->validate(['role' => ['nullable', 'exists:roles,name']]);

        $admin->syncRoles($data['role'] ? [$data['role']] : []);

        return back()->with('success', "Đã cập nhật vai trò cho {$admin->name}.");
    }
}
