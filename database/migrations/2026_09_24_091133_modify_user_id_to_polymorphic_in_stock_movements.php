<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tách ra 2 block Schema::table để tránh lỗi DB Engine khi vừa đổi tên vừa thêm cột
        Schema::table('stock_movements', function (Blueprint $table) {
            // 1. Gỡ khóa ngoại cứng
            $table->dropForeign('stock_movements_user_id_foreign');
            // 2. Đổi tên cột user_id thành causer_id (ID của người/vật gây ra hành động)
            $table->renameColumn('user_id', 'causer_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            // 3. Thêm cột causer_type để lưu tên Model (App\Models\Admin hoặc App\Models\User)
            $table->string('causer_type')->nullable()->after('causer_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('causer_type');
            $table->renameColumn('causer_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};