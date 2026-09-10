<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - MommyKids Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coral: { DEFAULT: '#ff4757', light: '#ffeaa7', dark: '#e84118' },
                        ink: { DEFAULT: '#2d3436', soft: '#636e72' },
                        'admin-bg': '#f8fafc',
                        'admin-border': '#e2e8f0'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">MommyKids Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Đăng nhập tài khoản quản trị viên</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-red-600 text-xs font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.auth.login') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email quản trị</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@mommykids.vn"
                       class="w-full h-11 px-4 rounded-xl border border-slate-200 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mật khẩu</label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full h-11 px-4 rounded-xl border border-slate-200 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 transition">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 rounded text-red-500 border-slate-300 focus:ring-red-400 cursor-pointer">
                <label for="remember" class="ml-2 text-xs font-medium text-slate-600 cursor-pointer">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit"
                    class="w-full h-11 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-xl transition shadow-md shadow-red-200 cursor-pointer">
                Đăng nhập
            </button>
        </form>
    </div>

</body>
</html>