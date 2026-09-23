<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_voucher')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['is_active', 'starts_at', 'ends_at'],
                'campaigns_running_idx'
            );

            $table->index(
                ['type', 'is_active'],
                'campaigns_type_active_idx'
            );
        });

        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();

            $table->unsignedInteger('stock_limit')->nullable();
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->unsignedInteger('max_per_user')->nullable();

            $table->timestamps();

            $table->unique(
                ['campaign_id', 'product_id'],
                'campaign_product_unique'
            );

            $table->index(
                ['product_id', 'campaign_id'],
                'campaign_product_lookup_idx'
            );
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                ->nullable()
                ->constrained('campaigns')
                ->nullOnDelete();

            $table->string('campaign_type', 50)
                ->nullable();

            $table->unsignedBigInteger('campaign_discount_amount')
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);

            $table->dropColumn([
                'campaign_id',
                'campaign_type',
                'campaign_discount_amount',
            ]);
        });

        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaigns');
    }
};
