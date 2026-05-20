<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('emoji', 10)->default('🏆');
            $table->enum('rarity', ['Common', 'Rare', 'Epic', 'Legendary'])->default('Common');
            $table->string('category')->nullable();
            $table->unsignedInteger('xp_bonus')->default(0);
            $table->string('condition_type')->nullable(); // 'quiz_count', 'streak', 'xp', 'level'
            $table->unsignedInteger('condition_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
