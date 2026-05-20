<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'ready_to_claim' to the status enum
        DB::statement("ALTER TABLE mission_progress MODIFY COLUMN status ENUM('pending', 'in_progress', 'ready_to_claim', 'completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert: first reset any ready_to_claim rows back to pending to avoid truncation
        DB::statement("UPDATE mission_progress SET status = 'pending' WHERE status = 'ready_to_claim'");
        DB::statement("ALTER TABLE mission_progress MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
