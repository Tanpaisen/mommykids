<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            // Cho phép user_id nullable để khách vãng lai cũng có thể thêm vào giỏ
            $table->foreignUlid('user_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Bổ sung cột session_id
            $table->string('session_id')->nullable()->index();
            
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};