<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - MommyKids</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-800 rounded-3xl shadow-2xl p-8 border border-slate-700">
        <div class="text-center mb-8">
            <span class="text-3xl font-black text-rose-500 tracking-tight">Mommy<span class="text-amber-400">Kids</span></span>
            <span class="block text-xs uppercase tracking-widest text-slate-400 mt-1 font-semibold">Admin System</span>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-500/10 text-rose-400 text-sm border border-rose-500/20">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Quản trị</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@mommykids.vn" 
                       class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Mật khẩu</label>
                <input type="password" name="password" required 
                       placeholder="••••••••" 
                       class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition text-sm">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember_admin" class="w-4 h-4 text-rose-500 rounded border-slate-700 bg-slate-900 focus:ring-rose-500">
                <label for="remember_admin" class="ml-2 text-sm text-slate-400">Duy trì đăng nhập</label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-900/30 transition duration-200">
                Vào Hệ Thống Admin
            </button>
        </form>
    </div>

</body>
</html>