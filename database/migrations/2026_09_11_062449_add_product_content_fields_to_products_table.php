<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('description');
            $table->string('manufacturer')->nullable()->after('origin');

            $table->text('ingredients')
                ->nullable()
                ->after('manufacturer');

            $table->text('usage_instructions')
                ->nullable()
                ->after('ingredients');

            $table->text('storage_instructions')
                ->nullable()
                ->after('usage_instructions');

            $table->text('warning')
                ->nullable()
                ->after('storage_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'origin',
                'manufacturer',
                'ingredients',
                'usage_instructions',
                'storage_instructions',
                'warning',
            ]);
        });
    }
};