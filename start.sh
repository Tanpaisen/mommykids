#!/bin/sh

# 1. Thực thi các lệnh chạy 1 lần lúc ứng dụng vừa khởi động (lúc này đã nhận đủ Env từ Railway)
echo "Đang khởi tạo cache..."
php artisan optimize
# php artisan migrate --force  (Thường các lệnh migrate database cũng được để ở đây)
# php artisan cache:warm-up-all (Nếu bạn tự viết command này)

# 2. Sau khi dọn dẹp và chuẩn bị xong, bàn giao toàn quyền lại cho Supervisor
echo "Khởi động Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf