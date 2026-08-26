<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ORD-20240001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Người nhận
            $table->string('recipient_name');
            $table->string('recipient_phone', 20);
            $table->string('recipient_email')->nullable();

            // Địa chỉ giao hàng
            $table->string('province_name');
            $table->string('district_name');
            $table->string('ward_name');
            $table->string('address_detail'); // số nhà, tên đường
            $table->unsignedInteger('ghn_province_id')->nullable();
            $table->unsignedInteger('ghn_district_id')->nullable();
            $table->string('ghn_ward_code', 20)->nullable();

            // Tiền
            $table->unsignedBigInteger('subtotal');      // tổng sản phẩm
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');

            // Trạng thái
            $table->enum('status', [
                'pending','confirmed','processing',
                'shipping','delivered','cancelled','refunded'
            ])->default('pending');

            $table->enum('payment_method', ['cod','vnpay','qr'])->default('cod');
            $table->enum('payment_status', ['unpaid','paid','refunded'])->default('unpaid');

            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');      // snapshot tên lúc mua
            $table->string('product_sku')->nullable();
            $table->unsignedBigInteger('price'); // snapshot giá lúc mua
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();

            // GHN
            $table->string('ghn_order_code')->nullable();    // mã vận đơn GHN
            $table->string('tracking_number')->nullable();   // alias
            $table->string('carrier')->default('GHN');

            $table->enum('service_type', ['standard','economy'])->default('standard');
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedInteger('weight')->default(500); // gram
            $table->unsignedInteger('length')->default(20);  // cm
            $table->unsignedInteger('width')->default(15);
            $table->unsignedInteger('height')->default(10);

            $table->enum('status', [
                'pending','picked','storing','delivering',
                'delivered','return','cancel'
            ])->default('pending');

            $table->timestamp('expected_delivery_at')->nullable();
            $table->json('ghn_response')->nullable(); // raw response lưu debug
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};