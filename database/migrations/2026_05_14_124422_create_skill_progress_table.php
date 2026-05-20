<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->enum('category', ['Logic', 'Creativity', 'Memory', 'Communication', 'Leadership', 'Problem Solving', 'Focus', 'Innovation']);
            $table->unsignedInteger('score');
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['child_profile_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_progress');
    }
};
