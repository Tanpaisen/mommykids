<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('recipient_name', 100);
            $table->string('phone', 20);

            $table->unsignedInteger('province_id');
            $table->string('province_name', 120);

            $table->unsignedInteger('district_id');
            $table->string('district_name', 120);

            $table->string('ward_code', 30);
            $table->string('ward_name', 120);

            $table->string('address_detail', 255);

            $table->string('label', 50)
                ->nullable();

            $table->boolean('is_default')
                ->default(false);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_default',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};