<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - MommyKids</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-lg border border-slate-200">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">MommyKids Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Đăng nhập tài khoản quản trị viên</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email quản trị</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@mommykids.vn"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none text-sm transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mật khẩu</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none text-sm transition">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <button type="submit" class="w-full py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl text-sm shadow transition duration-200">
                Đăng nhập
            </button>
        </form>
    </div>

</body>
</html>