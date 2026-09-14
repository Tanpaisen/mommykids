<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `shipments` MODIFY COLUMN `status` ENUM(
            'pending',
            'ready_to_pick',
            'picking',
            'cancel',
            'money_collect_picking',
            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering',
            'delivered',
            'delivery_fail',
            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail',
            'returned',
            'exception',
            'damage',
            'lost'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `shipments` MODIFY COLUMN `status` ENUM(
            'pending','picked','storing','delivering','delivered','return','cancel'
        ) NOT NULL DEFAULT 'pending'");
    }
};
