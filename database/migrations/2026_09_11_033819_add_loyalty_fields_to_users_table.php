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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'loyalty_code')) {
                $table->string('loyalty_code', 20)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'tier')) {
                $table->string('tier')->default('member')->after('loyalty_code');
            }
            if (!Schema::hasColumn('users', 'points')) {
                $table->unsignedBigInteger('points')->default(0)->after('tier');
            }
            if (!Schema::hasColumn('users', 'total_spent')) {
                $table->decimal('total_spent', 15, 2)->default(0)->after('points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = array_filter(
                ['loyalty_code', 'tier', 'points', 'total_spent'],
                fn($column) => Schema::hasColumn('users', $column)
            );

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};