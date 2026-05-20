<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->string('career_title');
            $table->string('career_emoji', 10)->default('🚀');
            $table->unsignedInteger('match_percentage')->default(0);
            $table->text('ai_reasoning')->nullable();
            $table->json('skills_needed')->nullable();
            $table->timestamp('suggested_at');
            $table->timestamps();
            $table->index('child_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_recommendations');
    }
};
