<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->boolean('require_save_to_user')
                ->default(false)
                ->comment('true=Phải lưu vào tài khoản mới dùng; false=Không cần lưu, tự xuất hiện khi đủ điều kiện');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('require_save_to_user');
        });
    }
};