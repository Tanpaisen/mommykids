<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng vouchers
        Schema::create('vouchers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            // ĐỊNH DANH
            $table->string('code')->unique()->comment('Mã voucher');
            $table->string('name')->comment('Tên chương trình');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            
            // THUỘC TÍNH NÂNG CAO
            $table->boolean('auto_apply')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->unsignedInteger('priority')->default(0);
            $table->enum('channel', ['all', 'web', 'app'])->default('all');
            
            // CẤU HÌNH GIẢM GIÁ
            $table->enum('discount_type', ['percent', 'fixed', 'free_shipping']);
            $table->unsignedBigInteger('discount_value')->default(0); 
            $table->unsignedBigInteger('max_discount_amount')->nullable();
            $table->unsignedBigInteger('min_order_amount')->default(0);
            
            // QUẢN LÝ LƯỢT DÙNG & NGÂN SÁCH
            $table->unsignedInteger('total_quantity')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedInteger('usage_limit_per_user')->default(1);
            $table->unsignedBigInteger('total_budget')->nullable();
            
            // PHÂN QUYỀN SỞ HỮU & ĐỐI TƯỢNG
            $table->enum('owner_type', ['platform', 'shop', 'partner'])->default('platform'); 
            $table->unsignedBigInteger('owner_id')->nullable(); 
            $table->enum('apply_to', ['all', 'new_user', 'specific_tiers', 'specific_users'])->default('all');
            
            // THỜI GIAN & TRẠNG THÁI
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'expired'])->default('draft');
            
            // AUDIT & METADATA
            $table->ulid('created_by')->nullable(); 
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'starts_at', 'expires_at']);
        });

        // 2. Bảng voucher_conditions
        Schema::create('voucher_conditions', function (Blueprint $table) {
            $table->id();
            // Lưu ý: foreignUlid vì vouchers.id là ULID
            $table->foreignUlid('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            
            $table->string('type'); 
            $table->enum('operator', ['eq', 'neq', 'in', 'not_in', 'gte', 'lte'])->default('eq');
            $table->string('value'); 
            $table->boolean('is_include')->default(true);
            
            $table->timestamps();
            $table->index(['voucher_id', 'type']);
        });

        // 3. Bảng voucher_users
        Schema::create('voucher_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            // Lưu ý: foreignUlid vì users.id là ULID
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->unique(['voucher_id', 'user_id']);
        });

        // 4. Bảng voucher_usages
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('voucher_id')->constrained('vouchers');
            $table->foreignUlid('user_id')->constrained('users');
            $table->foreignUlid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            
            // SNAPSHOTS
            $table->string('voucher_code');
            $table->string('voucher_name')->nullable();
            $table->string('discount_type');
            $table->unsignedBigInteger('discount_value');
            $table->unsignedBigInteger('order_subtotal')->nullable();
            $table->unsignedBigInteger('shipping_fee')->nullable();
            $table->unsignedBigInteger('discount_amount');
            $table->unsignedBigInteger('final_total')->nullable();
            
            // TRẠNG THÁI & VÒNG ĐỜI
            $table->enum('status', ['reserved', 'applied', 'completed', 'cancelled'])->default('reserved');
            
            $table->timestamp('reserved_until')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            
            $table->timestamps();
            $table->index(['voucher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('voucher_users');
        Schema::dropIfExists('voucher_conditions');
        Schema::dropIfExists('vouchers');
    }
};