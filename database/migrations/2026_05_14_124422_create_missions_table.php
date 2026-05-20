<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('emoji', 10)->default('🎯');
            $table->enum('category', ['Logic', 'Creativity', 'Memory', 'Communication', 'Leadership', 'Problem Solving', 'Focus', 'Innovation', 'General'])->default('General');
            $table->unsignedInteger('xp_reward')->default(20);
            $table->enum('type', ['daily', 'weekly'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
