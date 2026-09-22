<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('campaign_type_id')
                ->nullable()
                ->after('name')
                ->constrained('campaign_types')
                ->nullOnDelete();
        });

        /*
         * Tự chuyển các type cũ đang có trong campaigns sang campaign_types.
         * Không hard-code Flash Sale / Markdown trong migration.
         */
        $legacyTypes = DB::table('campaigns')
            ->whereNotNull('type')
            ->where('type', '<>', '')
            ->distinct()
            ->pluck('type');

        foreach ($legacyTypes as $legacyType) {
            $legacyType = (string) $legacyType;

            $typeId = DB::table('campaign_types')
                ->where('code', $legacyType)
                ->value('id');

            if (!$typeId) {
                $typeId = DB::table('campaign_types')->insertGetId([
                    'name' => Str::headline($legacyType),
                    'code' => $legacyType,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('campaigns')
                ->where('type', $legacyType)
                ->update([
                    'campaign_type_id' => $typeId,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['campaign_type_id']);
            $table->dropColumn('campaign_type_id');
        });

        Schema::dropIfExists('campaign_types');
    }
};
