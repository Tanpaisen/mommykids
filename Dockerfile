# Sử dụng PHP 8.2 CLI chính thức
FROM php:8.2-cli

# Cài đặt Node.js phiên bản mới nhất (Node 20.x) và Supervisor
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y \
       git \
       curl \
       zip \
       unzip \
       nodejs \
       libpq-dev \
       libpng-dev \
       libonig-dev \
       libxml2-dev \
       supervisor \
    && docker-php-ext-install pdo pdo_mysql sockets

# Cài đặt Composer chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy toàn bộ mã nguồn vào container
COPY . .

# Cài đặt các gói PHP (bỏ qua dev để tối ưu dung lượng)
RUN composer install --no-dev --optimize-autoloader

# Cài đặt gói Node và Build giao diện (Vite)
RUN npm install && npm run build

# Phân quyền lưu trữ cho Laravel
RUN chmod -R 777 storage bootstrap/cache

# Copy file cấu hình supervisor vào thư mục hệ thống
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Xóa cache config cũ để tránh nhận diện sai môi trường
# Xóa cache view và config để Laravel nhận diện giao diện và asset mới nhất
RUN php artisan view:clear && php artisan config:clear

# Khởi chạy ứng dụng thông qua Supervisor (quản lý cả Web và Reverb)
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Thêm pcntl vào danh sách các extension được cài đặt
RUN docker-php-ext-install pdo pdo_mysql sockets pcntl