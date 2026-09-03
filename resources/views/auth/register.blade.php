<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - MommyKids</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-rose-50/40 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl shadow-rose-100 p-8 border border-rose-100 my-8">
        <!-- Logo -->
        <div class="text-center mb-6">
            <a href="/" class="inline-block">
                <span class="text-3xl font-black text-rose-500 tracking-tight">Mommy<span class="text-amber-400">Kids</span></span>
            </a>
            <h2 class="text-xl font-bold text-gray-800 mt-3">Đăng ký tài khoản mới</h2>
            <p class="text-sm text-gray-500 mt-1">Tạo tài khoản để nhận nhiều ưu đãi mua sắm cho bé</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-2xl bg-rose-50 text-rose-600 text-sm border border-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="Nguyễn Văn A" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="ame@mommykids.vn" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       placeholder="0912345678" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu</label>
                <input type="password" name="password" required 
                       placeholder="Tối thiểu 8 ký tự" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required 
                       placeholder="Nhập lại mật khẩu" 
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 outline-none transition text-sm">
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-2xl shadow-lg shadow-rose-200 transition duration-200 active:scale-[0.98] mt-2">
                Đăng ký tài khoản
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            Đã có tài khoản? 
            <a href="{{ route('login') }}" class="font-bold text-rose-500 hover:underline">Đăng nhập ngay</a>
        </div>
    </div>

</body>
</html>