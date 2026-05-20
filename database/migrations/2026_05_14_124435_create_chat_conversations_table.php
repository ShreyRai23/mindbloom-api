<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->string('title')->default('Chat with Bloomy');
            $table->timestamps();
            $table->index('child_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
