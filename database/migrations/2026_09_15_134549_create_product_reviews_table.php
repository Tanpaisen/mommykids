<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            /*
             * users.id hiện tại là CHAR(26) vì User dùng ULID.
             */
            $table->char('user_id', 26);

            $table->unsignedTinyInteger('rating');

            $table->text('comment')
                ->nullable();

            $table->json('images')
                ->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique([
                'product_id',
                'user_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};