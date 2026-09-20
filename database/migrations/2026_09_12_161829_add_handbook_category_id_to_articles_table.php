<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'handbook_category_id')) {
                $table->foreignId('handbook_category_id')->nullable()->after('id')->constrained('handbook_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('articles', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('views');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['handbook_category_id']);
            $table->dropColumn(['handbook_category_id', 'sort_order']);
        });
    }
};