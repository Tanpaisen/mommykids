<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('client.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:nam,nu,khac',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
        }
        public function support()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('client.profile.support', compact('user'));
    }
    public function policy()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('client.profile.policy', compact('user'));
    }
}