<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Chỉ tạo cột lưu ID, KHÔNG tạo ràng buộc khóa ngoại cứng
            $table->unsignedBigInteger('user_id')->nullable();

            $table->enum('status', ['active', 'checked_out', 'abandoned'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};