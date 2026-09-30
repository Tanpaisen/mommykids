<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            // Thông tin thương hiệu
            if (!Schema::hasColumn('settings', 'site_name')) {
                $table->string('site_name')->nullable();
            }

            if (!Schema::hasColumn('settings', 'copyright')) {
                $table->text('copyright')->nullable();
            }

            // Liên hệ
            if (!Schema::hasColumn('settings', 'hotline')) {
                $table->string('hotline')->nullable();
            }

            if (!Schema::hasColumn('settings', 'email')) {
                $table->string('email')->nullable();
            }

            if (!Schema::hasColumn('settings', 'address')) {
                $table->text('address')->nullable();
            }

            // Mạng xã hội
            if (!Schema::hasColumn('settings', 'facebook_url')) {
                $table->string('facebook_url')->nullable();
            }

            if (!Schema::hasColumn('settings', 'zalo_url')) {
                $table->string('zalo_url')->nullable();
            }

            if (!Schema::hasColumn('settings', 'instagram_url')) {
                $table->string('instagram_url')->nullable();
            }


            // Thanh thông báo đầu trang
            if (!Schema::hasColumn('settings', 'top_announcement')) {
                $table->text('top_announcement')->nullable();
            }


            // Cấu hình giao diện
            if (!Schema::hasColumn('settings', 'default_location')) {
                $table->string('default_location')->nullable();
            }

            if (!Schema::hasColumn('settings', 'search_placeholder')) {
                $table->text('search_placeholder')->nullable();
            }

            if (!Schema::hasColumn('settings', 'home_banner_title')) {
                $table->text('home_banner_title')->nullable();
            }


            // Khối khuyến mãi
            if (!Schema::hasColumn('settings', 'promo_title')) {
                $table->text('promo_title')->nullable();
            }

            if (!Schema::hasColumn('settings', 'promo_subtitle')) {
                $table->text('promo_subtitle')->nullable();
            }

            if (!Schema::hasColumn('settings', 'promo_badge_1')) {
                $table->string('promo_badge_1')->nullable();
            }

            if (!Schema::hasColumn('settings', 'promo_badge_2')) {
                $table->string('promo_badge_2')->nullable();
            }

            if (!Schema::hasColumn('settings', 'promo_badge_3')) {
                $table->string('promo_badge_3')->nullable();
            }

            if (!Schema::hasColumn('settings', 'promo_button_text')) {
                $table->string('promo_button_text')->nullable();
            }


            // Footer + SEO
            if (!Schema::hasColumn('settings', 'footer_description')) {
                $table->text('footer_description')->nullable();
            }

            if (!Schema::hasColumn('settings', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }

            if (!Schema::hasColumn('settings', 'header_scripts')) {
                $table->longText('header_scripts')->nullable();
            }

        });
    }


    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            $columns = [
                'site_name',
                'copyright',
                'hotline',
                'email',
                'address',
                'facebook_url',
                'zalo_url',
                'instagram_url',
                'top_announcement',
                'default_location',
                'search_placeholder',
                'home_banner_title',
                'promo_title',
                'promo_subtitle',
                'promo_badge_1',
                'promo_badge_2',
                'promo_badge_3',
                'promo_button_text',
                'footer_description',
                'meta_description',
                'header_scripts'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }

        });
    }
};