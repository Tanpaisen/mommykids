<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('chat_bot_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name',150);
            $table->json('keywords');
            $table->text('response');
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('handoff_to_staff')->default(false);
            $table->unsignedBigInteger('matched_count')->default(0);
            $table->timestamps();
            $table->index(['is_active','priority']);
        });
    }
    public function down(): void { Schema::dropIfExists('chat_bot_scenarios'); }
};
