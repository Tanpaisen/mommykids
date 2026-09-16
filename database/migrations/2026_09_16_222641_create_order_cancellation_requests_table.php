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
    Schema::create('order_cancellation_requests', function (Blueprint $table) {
        $table->ulid('id')->primary();

        $table->foreignUlid('order_id')
            ->constrained('orders')
            ->cascadeOnDelete();

        $table->foreignUlid('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->text('reason');

        $table->string('status', 20)
            ->default('pending');

        $table->text('admin_note')
            ->nullable();

        $table->timestamp('processed_at')
            ->nullable();

        $table->timestamps();

        $table->index(['user_id', 'status']);
        $table->index(['order_id', 'status']);
    });
}
};