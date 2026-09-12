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
        if (!Schema::hasTable('point_logs')) {
            Schema::create('point_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
                $table->integer('points'); // Số điểm cộng (+10, +50) hoặc trừ (-20, -100)
                $table->string('type'); // 'earn' (tích điểm), 'redeem' (tiêu điểm), 'refund' (hoàn điểm)
                $table->string('description'); // Mô tả giao dịch (VD: Tích điểm đơn hàng #1002)
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_logs');
    }
};