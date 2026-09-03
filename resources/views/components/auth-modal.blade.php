<!-- Modal Đăng nhập Client -->
<div id="loginModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl flex flex-col md:flex-row relative">
        
        <!-- Nút Đóng Modal -->
        <button onclick="closeLoginModal()" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold z-10">&times;</button>

        <!-- Bên trái: Banner Đỏ -->
        <div class="md:w-1/2 bg-rose-600 p-6 text-white flex flex-col items-center justify-center text-center relative overflow-hidden">
            <h3 class="text-2xl font-black mb-1">100%</h3>
            <p class="text-xl font-bold uppercase tracking-wide">Hàng Chính Hãng</p>
            <div class="mt-4 w-40 h-56 bg-rose-500 rounded-2xl border-4 border-white/20 shadow-inner flex items-center justify-center p-2">
                 <span class="text-xs text-rose-100 font-semibold">MommyKids App</span>
            </div>
        </div>

        <!-- Bên phải: Form Đăng nhập Email OTP -->
        <div class="md:w-1/2 p-6 md:p-8 flex flex-col justify-center">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-rose-600">ĐĂNG NHẬP</h2>
                <p class="text-xs text-gray-500 mt-1">Hãy trở thành thành viên để không bỏ lỡ các ưu đãi, giảm giá và voucher dành riêng cho bạn</p>
            </div>

            <!-- Thông báo lỗi/thành công -->
            <div id="modalAlert" class="hidden mb-3 p-2 text-xs rounded-xl text-center"></div>

            <form id="otpLoginForm" onsubmit="handleVerifyOtp(event)" class="space-y-4">
                @csrf
                <!-- Nhập Email & Nút gửi OTP -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Địa chỉ Email *</label>
                    <div class="flex gap-2">
                        <input type="email" id="clientEmail" required placeholder="Vui lòng nhập Email của Bạn" 
                               class="w-full text-xs px-3 py-2.5 rounded-xl border border-gray-300 focus:border-rose-500 outline-none">
                        <button type="button" id="btnSendOtp" onclick="handleSendOtp()" 
                                class="bg-gray-100 hover:bg-gray-200 text-rose-600 text-xs font-bold px-3 py-2 rounded-xl whitespace-nowrap transition border border-gray-200">
                            Gửi OTP
                        </button>
                    </div>
                </div>

                <!-- 6 ô nhập mã OTP -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mã xác thực *</label>
                    <div class="grid grid-cols-6 gap-2" id="otpInputs">
<input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        <input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        <input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        <input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        <input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        <input type="text" maxlength="1" class="otp-box w-full h-10 text-center font-bold text-lg border border-gray-300 rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                    </div>
                </div>

                <!-- Checkbox Điều khoản -->
                <div class="flex items-start gap-2 pt-1">
                    <input type="checkbox" id="terms" required class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                    <label for="terms" class="text-[10px] text-gray-500 leading-tight">
                        Tôi đã đọc và đồng ý với các <a href="#" class="text-rose-600 underline">Điều khoản sử dụng</a> và <a href="#" class="text-rose-600 underline">Chính sách bảo mật</a> của MommyKids
                    </label>
                </div>

                <!-- Nút Đăng Nhập -->
                <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-full text-sm shadow-md transition">
                    ĐĂNG NHẬP
                </button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Xử lý AJAX Không Load Trang -->
<script>
    function openLoginModal() { document.getElementById('loginModal').classList.remove('hidden'); }
    function closeLoginModal() { document.getElementById('loginModal').classList.add('hidden'); }

    // Tự động nhảy ô khi nhập 6 số OTP
    const boxes = document.querySelectorAll('.otp-box');
    boxes.forEach((box, idx) => {
        box.addEventListener('input', (e) => {
            if (e.target.value && idx < boxes.length - 1) boxes[idx + 1].focus();
        });
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) boxes[idx - 1].focus();
        });
    });
function showAlert(msg, isSuccess) {
        const el = document.getElementById('modalAlert');
        el.className = `mb-3 p-2 text-xs rounded-xl text-center ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
        el.innerText = msg;
        el.classList.remove('hidden');
    }

    // AJAX 1: Gửi OTP
    async function handleSendOtp() {
        const email = document.getElementById('clientEmail').value;
        if (!email) return showAlert('Vui lòng nhập Email!', false);

        const btn = document.getElementById('btnSendOtp');
        btn.disabled = true;
        btn.innerText = 'Đang gửi...';

        try {
            const res = await fetch('{{ route("api.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            showAlert(data.message, data.success);

            if (data.success) {
                let timer = 60;
                const interval = setInterval(() => {
                    btn.innerText = `Thử lại (${timer--}s)`;
                    if (timer < 0) {
                        clearInterval(interval);
                        btn.innerText = 'Gửi OTP';
                        btn.disabled = false;
                    }
                }, 1000);
            } else {
                btn.disabled = false;
                btn.innerText = 'Gửi OTP';
            }
        } catch (err) {
            showAlert('Lỗi kết nối máy chủ!', false);
            btn.disabled = false;
            btn.innerText = 'Gửi OTP';
        }
    }

    // AJAX 2: Xác thực & Đăng nhập không reload trang
    async function handleVerifyOtp(e) {
        e.preventDefault();
        const email = document.getElementById('clientEmail').value;
        let otp = '';
        boxes.forEach(box => otp += box.value);

        if (otp.length < 6) return showAlert('Vui lòng nhập đủ 6 số OTP!', false);

        try {
            const res = await fetch('{{ route("api.verify-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email, otp })
            });
            const data = await res.json();

            if (data.success) {
                showAlert('Đăng nhập thành công!', true);
                setTimeout(() => {
                    closeLoginModal();
                    window.location.reload(); // Cập nhật Header thành trạng thái đã đăng nhập
                }, 800);
            } else {
                showAlert(data.message, false);
            }
        } catch (err) {
showAlert('Lỗi xác thực OTP!', false);
        }
    }
</script>