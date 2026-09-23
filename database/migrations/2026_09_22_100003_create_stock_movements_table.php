<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bước 3: Tạo bảng lưu vết biến động kho (stock_movements)
     * Mọi thay đổi tồn kho PHẢI ghi log vào đây — chống gian lận, đối soát.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('variant_id')->nullable()->comment('ID biến thể (nếu có)');

            // Loại biến động
            $table->enum('type', ['import', 'export', 'return', 'damage', 'adjust'])
                ->comment('import=nhập hàng, export=xuất bán, return=khách trả, damage=hỏng/mất, adjust=admin chỉnh tay');

            $table->integer('quantity')->comment('Số lượng thay đổi (+40 hoặc -1)');
            $table->integer('stock_before')->comment('Tồn kho TRƯỚC khi biến động');
            $table->integer('stock_after')->comment('Tồn kho SAU khi biến động');

            // Đối soát với Order / PurchaseOrder
            $table->string('reference_type')->nullable()->comment('App\Models\Order / PurchaseOrder');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID đơn hàng / phiếu nhập');

            // Chống gian lận nội bộ
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Người thực hiện thao tác');
            $table->string('note')->nullable()->comment('Lý do (bắt buộc khi adjust)');

            $table->timestamps();

            // Index tối ưu truy vấn
            $table->index(['product_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
