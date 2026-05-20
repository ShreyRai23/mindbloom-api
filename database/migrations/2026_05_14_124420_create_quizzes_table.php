<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['Logic', 'Creativity', 'Memory', 'Communication', 'Leadership', 'Problem Solving', 'Focus', 'Innovation']);
            $table->text('description')->nullable();
            $table->string('emoji', 10)->default('🎯');
            $table->unsignedInteger('xp_reward')->default(50);
            $table->enum('difficulty', ['Easy', 'Medium', 'Hard'])->default('Easy');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
