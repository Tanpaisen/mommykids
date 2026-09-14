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
            ENUM('cod','vnpay','qr','momo','zalopay','stripe')
            NOT NULL DEFAULT 'cod'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY payment_method
            ENUM('cod','vnpay','qr','momo','zalopay')
            NOT NULL DEFAULT 'cod'
        ");
    }
};