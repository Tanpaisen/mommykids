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
    && docker-php-ext-install pdo pdo_mysql sockets pcntl

# Cài đặt Composer chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy toàn bộ mã nguồn vào container
COPY . .

# Cài đặt các gói PHP (bỏ qua dev để tối ưu dung lượng)
RUN composer install --no-dev --optimize-autoloader

# --- PHẦN QUAN TRỌNG ĐỂ VITE NHẬN BIẾN TỪ RAILWAY ---
ARG VITE_REVERB_APP_KEY
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY

ARG VITE_REVERB_HOST
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST

ARG VITE_REVERB_PORT
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT

ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
# ----------------------------------------------------

# Cài đặt gói Node và Build giao diện (Vite)
RUN npm install && npm run build

# Phân quyền lưu trữ cho Laravel và CẤP QUYỀN THỰC THI CHO SCRIPT
RUN chmod -R 777 storage bootstrap/cache \
    && chmod +x start.sh

# Copy file cấu hình supervisor vào thư mục hệ thống
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Xóa cache config cũ để tránh nhận diện sai môi trường
RUN php artisan view:clear && php artisan config:clear

# KHỞI CHẠY ỨNG DỤNG QUA SCRIPT THAY VÌ GỌI THẲNG SUPERVISOR
CMD ["./start.sh"]