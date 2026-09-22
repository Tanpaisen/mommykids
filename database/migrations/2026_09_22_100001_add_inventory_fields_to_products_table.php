<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('id')->comment('Mã SKU duy nhất');
            }
            
            if (!Schema::hasColumn('products', 'code')) {
                $table->string('code')->nullable()->after('id')->comment('Mã nhà cung cấp / Barcode');
            }

            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->unsignedBigInteger('cost_price')->default(0)->after('price')->comment('Giá vốn / Giá nhập');
            }

            if (!Schema::hasColumn('products', 'low_stock_alert')) {
                $table->unsignedInteger('low_stock_alert')->default(5)->after('stock')->comment('Cảnh báo khi tồn kho dưới mức này');
            }

            if (!Schema::hasColumn('products', 'weight_grams')) {
                $table->unsignedInteger('weight_grams')->default(0)->after('stock')->comment('Khối lượng (gam) để tính phí ship');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('products', 'sku')) $columnsToDrop[] = 'sku';
            if (Schema::hasColumn('products', 'code')) $columnsToDrop[] = 'code';
            if (Schema::hasColumn('products', 'cost_price')) $columnsToDrop[] = 'cost_price';
            if (Schema::hasColumn('products', 'low_stock_alert')) $columnsToDrop[] = 'low_stock_alert';
            if (Schema::hasColumn('products', 'weight_grams')) $columnsToDrop[] = 'weight_grams';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};