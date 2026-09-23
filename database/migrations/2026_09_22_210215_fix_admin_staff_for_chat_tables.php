<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            // staff_id trước đây trỏ nhầm sang users.id
            $table->dropForeign(['staff_id']);

            // staff thực tế là tài khoản Admin
            $table->foreign('staff_id')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            // sender_id tiếp tục dành cho khách hàng (users.id)
            // admin_id dành riêng cho tin nhắn của nhân viên/admin
            $table->foreignUlid('admin_id')
                ->nullable()
                ->after('sender_id')
                ->constrained('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);

            // Khôi phục cấu trúc cũ nếu rollback
            $table->foreign('staff_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};