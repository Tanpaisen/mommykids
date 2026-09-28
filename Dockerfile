# Sử dụng PHP 8.2 CLI chính thức
FROM php:8.2-cli

# Cài đặt Node.js phiên bản mới nhất (Node 20.x) từ trang chủ NodeSource
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

# Render yêu cầu lắng nghe trên cổng động được truyền qua biến môi trường $PORT (mặc định là 10000)
# Thay vì dùng php artisan serve, dùng lệnh php -S (Built-in server của PHP) sẽ ổn định hơn trên cloud
CMD sh -c "php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:${PORT:-10000} -t public"