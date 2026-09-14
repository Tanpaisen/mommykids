<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - MommyKids</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-rose-50/40 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl shadow-rose-100 p-8 border border-rose-100">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-block">
                <span class="text-3xl font-black text-rose-500 tracking-tight">Mommy<span class="text-amber-400">Kids</span></span>
            </a>
            <h2 class="text-xl font-bold text-gray-800 mt-4">Chào mừng ba mẹ trở lại!</h2>
            <p class="text-sm text-gray-500 mt-1">Đăng nhập tài khoản để nhận ưu đãi cho bé</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-2xl bg-rose-50 text-rose-600 text-sm border border-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="vass@mommykids.vn" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Mật khẩu</label>
                    <a href="#" class="text-xs font-semibold text-rose-500 hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" required 
                       placeholder="••••••••" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-rose-500 rounded border-gray-300 focus:ring-rose-400">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-2xl shadow-lg shadow-rose-200 transition duration-200 active:scale-[0.98]">
                Đăng nhập
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-gray-500">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="font-bold text-rose-500 hover:underline">Đăng ký ngay</a>
        </div>
    </div>

</body>
</html>