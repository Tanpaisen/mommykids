<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_menus', function (Blueprint $table) {
            $table->id();
            $table->string('group_name'); // Tên nhóm (VD: DASHBOARD & THỐNG KÊ)
            $table->string('title');      // Tên hiển thị (VD: Tổng quan, Sản phẩm)
            $table->string('route_name'); // Đường dẫn route cố định
            $table->integer('order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true); // Trạng thái ẩn/hiện
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menus');
    }
};