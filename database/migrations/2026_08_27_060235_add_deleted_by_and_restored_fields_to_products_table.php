<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('deleted_by')
                ->nullable()
                ->after('deleted_at');

            $table->unsignedBigInteger('restored_by')
                ->nullable()
                ->after('deleted_by');

            $table->timestamp('restored_at')
                ->nullable()
                ->after('restored_by');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'deleted_by',
                'restored_by',
                'restored_at',
            ]);
        });
    }
};