<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->text('summary');
            $table->string('top_strength')->nullable();
            $table->string('learning_style')->nullable();
            $table->string('personality_type')->nullable();
            $table->json('strengths_json')->nullable();
            $table->json('weaknesses_json')->nullable();
            $table->json('recommendations_json')->nullable();
            $table->json('skill_scores_snapshot')->nullable();
            $table->date('report_date');
            $table->timestamps();
            $table->index('child_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reports');
    }
};
