<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('mission_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->date('assigned_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['child_profile_id', 'mission_id', 'assigned_date'], 'mp_child_mission_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_progress');
    }
};
