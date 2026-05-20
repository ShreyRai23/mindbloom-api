<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('grade')->nullable();
            $table->string('hero_name')->nullable();
            $table->string('avatar_emoji', 10)->default('🦊');
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('coins')->default(0);
            $table->unsignedInteger('streak_count')->default(0);
            $table->date('last_active_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profiles');
    }
};
