<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY payment_method
            ENUM(
                'cod',
                'vnpay',
                'qr',
                'momo',
                'zalopay',
                'stripe',
                'paypal'
            )
            NOT NULL
            DEFAULT 'cod'
        ");
    }

    public function down(): void
    {
        // Tránh rollback lỗi nếu đã có đơn PayPal.
        DB::table('orders')
            ->where('payment_method', 'paypal')
            ->update([
                'payment_method' => 'cod',
            ]);

        DB::statement("
            ALTER TABLE orders
            MODIFY payment_method
            ENUM(
                'cod',
                'vnpay',
                'qr',
                'momo',
                'zalopay',
                'stripe'
            )
            NOT NULL
            DEFAULT 'cod'
        ");
    }
};