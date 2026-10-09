#!/bin/sh
set -e

echo "Khởi tạo MommyKids..."
php artisan optimize

(
    sleep 10

    echo "Đang warm cache nền..."

    if php artisan cache:warm --ghn-delay=100; then
        echo "Warm cache nền thành công."
    else
        echo "Warm cache nền có lỗi; giữ cache GHN cũ và ứng dụng vẫn tiếp tục chạy."
    fi
) &

echo "Khởi động Supervisor..."
exec /usr/bin/supervisord \
    -c /etc/supervisor/conf.d/supervisord.conf