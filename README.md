```markdown
# MommyKids 👶🍼

**MommyKids** là hệ thống thương mại điện tử chuyên cung cấp các sản phẩm chăm sóc mẹ và bé, được xây dựng mạnh mẽ trên nền tảng **Laravel 10**.

Hệ thống cung cấp trải nghiệm mua sắm hiện đại, mượt mà và hỗ trợ đầy đủ các nghiệp vụ từ giỏ hàng, quản lý đơn hàng, tính phí vận chuyển tự động, thanh toán trực tuyến, áp dụng voucher, tích điểm thưởng cho đến hệ thống thông báo realtime và trang quản trị (Admin Dashboard) chuyên nghiệp.

---

## 🌐 Website Demo

*   **Website chính thức:** [https://mommykids.up.railway.app](https://mommykids.up.railway.app)

---

## 🛠 Công nghệ sử dụng (Tech Stack)

Hệ thống được thiết kế theo kiến trúc 3-Tier (Presentation, Application/Logic, Data), bao gồm:

*   **Backend:** PHP 8.x, Laravel 10.
*   **Frontend:** Blade Templates, ReactJS (tích hợp qua Vite).
*   **Cơ sở dữ liệu:** MySQL (hoặc TiDB Cloud Serverless).
*   **Cache & Hàng đợi (Queue):** Redis.
*   **Real-time & WebSockets:** Laravel Reverb.
*   **Deployment:** Railway.

---

## 🚀 Các tính năng nổi bật

### 🛍️ Dành cho Khách hàng (User)
*   **Mua sắm & Giỏ hàng:** Tìm kiếm sản phẩm, xem chi tiết, thêm vào giỏ hàng và quản lý giỏ hàng thông minh.
*   **Vận chuyển tự động:** Tích hợp API **Giao Hàng Nhanh (GHN)** để lấy danh sách Tỉnh/Huyện/Xã chính xác và tính phí ship theo thời gian thực dựa trên khối lượng.
*   **Thanh toán đa dạng:** Hỗ trợ đa cổng thanh toán bao gồm:
    *   Thanh toán khi nhận hàng (COD).
    *   Chuyển khoản ngân hàng tự động (VietQR / tích hợp SePay).
    *   ZaloPay (Sandbox).
    *   Thẻ quốc tế Visa/Mastercard qua Stripe (Sandbox).
    *   PayPal (Sandbox).
*   **Ưu đãi & Điểm thưởng:** 
    *   Cơ chế Voucher thông minh (áp dụng song song mã giảm giá đơn hàng và mã miễn phí vận chuyển).
    *   Hệ thống tích luỹ điểm thưởng khi mua hàng và quy đổi điểm để giảm giá trực tiếp.
*   **Quản lý tài khoản:** Quản lý thông tin cá nhân, sổ địa chỉ nhận hàng, theo dõi trạng thái đơn hàng.

### ⚙️ Dành cho Quản trị viên (Admin)
*   **Dashboard tổng quan:** Thống kê doanh thu, đơn hàng, sản phẩm bán chạy.
*   **Quản lý Sản phẩm & Tồn kho:** Thêm, sửa, xóa sản phẩm, danh mục, cập nhật tồn kho.
*   **Xử lý Đơn hàng:** Xác nhận đơn, cập nhật trạng thái giao hàng, tạo vận đơn đồng bộ với GHN.
*   **Quản lý Khuyến mãi:** Tạo các chiến dịch (Campaign) giảm giá, quản lý mã Voucher.
*   **Quản lý Khách hàng:** Xem thông tin, lịch sử mua hàng và điểm tích lũy của khách.

---

## 💻 Hướng dẫn cài đặt môi trường Local

Để chạy dự án trên máy tính cá nhân, bạn cần cài đặt sẵn: **PHP >= 8.1**, **Composer**, **Node.js**, **MySQL** và **Redis**.

**Bước 1: Clone dự án về máy**
```bash
git clone [https://github.com/Tanpaisen/mommykids.git](https://github.com/Tanpaisen/mommykids.git)
cd mommykids

```

**Bước 2: Cài đặt các thư viện (Dependencies)**

```bash
composer install
npm install

```

**Bước 3: Cấu hình môi trường (.env)**
Copy file `.env.example` thành `.env` và cập nhật các thông số kết nối Database, Redis, và các API Keys (GHN, ZaloPay, Stripe...):

```bash
cp .env.example .env
php artisan key:generate

```

**Bước 4: Chạy Migration và Seed dữ liệu mẫu**

```bash
php artisan migrate --seed

```

**Bước 5: Chạy các dịch vụ (Mở nhiều terminal)**

```bash
# Terminal 1: Chạy server Laravel
php artisan serve

# Terminal 2: Biên dịch Frontend (Vite)
npm run dev

# Terminal 3: Chạy Queue Worker (xử lý email, job ngầm)
php artisan queue:work

# Terminal 4: Chạy WebSocket Server (Thông báo realtime)
php artisan reverb:start --port=8081 --debug

```

*Truy cập dự án tại: `http://localhost:8000*`

---

*Phát triển bởi [Lê Minh Tân] trưởng nhóm(https://www.google.com/search?q=https://github.com/Tanpaisen) và Nhóm phát triển MommyKids(4 thành viên TÚ, Tô TUẤN, ANH TUẤN).*

```

```
