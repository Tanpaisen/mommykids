<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vouchers', 'is_stackable')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('is_stackable');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('vouchers', 'is_stackable')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->boolean('is_stackable')->default(false)->after('auto_apply');
            });
        }
    }
};
