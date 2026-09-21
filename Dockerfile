FROM php:8.2-cli
# Cài đặt các thư viện cần thiết cho Laravel, MySQL và Nodejs (Vite)
RUN apt-get update && apt-get install -y git curl zip unzip nodejs npm \
    && docker-php-ext-install pdo pdo_mysql
# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
# Cài đặt gói mở rộng và đóng gói giao diện
RUN composer install --optimize-autoloader
RUN npm install && npm run build
# Mở cổng và khởi chạy server
CMD php artisan serve --host=0.0.0.0 --port=$PORT