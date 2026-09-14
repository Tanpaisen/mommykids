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
        // Bảng đã tồn tại trong DB, migration này chỉ để ghi nhận
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('ghn_order_code')->nullable()->unique();
                $table->string('tracking_number')->nullable();
                $table->string('carrier')->nullable();
                $table->string('service_type')->nullable();
                $table->unsignedBigInteger('shipping_fee')->default(0);
                $table->unsignedInteger('weight')->nullable();
                $table->unsignedInteger('length')->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('expected_delivery_at')->nullable();
                $table->json('ghn_response')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
