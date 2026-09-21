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
        Schema::table('vouchers', function (Blueprint $table) {
            // Thêm lại cột is_stackable, đặt vị trí tuỳ ý (ví dụ sau auto_apply)
            $table->boolean('is_stackable')->default(false)->after('auto_apply');
        });

        Schema::table('voucher_conditions', function (Blueprint $table) {
            // Dành cho điều kiện phức tạp: tham số phụ, khoảng giá trị, cấu hình đặc biệt...
            $table->json('payload')
                ->nullable()
                ->after('value')
                ->comment('Dữ liệu mở rộng cho điều kiện phức tạp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('is_stackable');
        });
        Schema::table('voucher_conditions', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
};