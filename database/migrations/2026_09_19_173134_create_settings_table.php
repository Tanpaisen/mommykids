<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // TAB 1: CẤU HÌNH CHUNG & LIÊN HỆ
            $table->string('site_name')->default('MommyKids');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('copyright')->default('© 2026 MommyKids. Đã đăng ký bản quyền.');
            $table->string('hotline')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('zalo_url')->nullable();
            $table->string('instagram_url')->nullable();

            // TAB 2: CẤU HÌNH TEXT & GIAO DIỆN
            $table->string('top_announcement')->nullable();
            $table->string('default_location')->default('Hà Nội');
            $table->string('search_placeholder')->default('Ba mẹ cần tìm gì cho bé hôm nay?');
            $table->string('home_banner_title')->nullable();
            $table->string('promo_title')->default('Ưu đãi dành cho ba mẹ');
            $table->string('promo_subtitle')->default('Nhập mã ngay để nhận ưu đãi cho lần mua đầu tiên');
            $table->string('promo_badge_1')->default('30K Voucher');
            $table->string('promo_badge_2')->default('-12% Tã & Bỉm');
            $table->string('promo_badge_3')->default('-15% Sữa bột');
            $table->string('promo_button_text')->default('Nhận ngay');
            $table->text('footer_description')->nullable();

            // TAB 3: SEO & MÃ NHÚNG
            $table->text('meta_description')->nullable();
            $table->text('header_scripts')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};