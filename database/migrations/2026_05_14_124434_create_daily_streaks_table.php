<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->date('streak_date');
            $table->unsignedInteger('xp_earned')->default(20);
            $table->timestamps();
            $table->unique(['child_profile_id', 'streak_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_streaks');
    }
};
