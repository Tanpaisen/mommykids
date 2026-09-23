<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bước 2: Bổ sung cost_price (snapshot giá vốn) vào order_items
     * Mục đích: Báo cáo lãi/lỗ KHÔNG BAO GIỜ bị sai lệch khi admin đổi giá vốn sau này.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // ✅ Quan trọng nhất: Giá vốn tại thời điểm bán (Snapshot kế toán)
            $table->unsignedBigInteger('cost_price')->default(0)->after('price')
                ->comment('Giá vốn tại thời điểm bán');

            // Tên biến thể (size/quy cách) — dùng khi có product_variants
            $table->string('variant_name')->nullable()->after('product_sku')
                ->comment('Tên biến thể: 900g, Size M...');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'variant_name']);
        });
    }
};
