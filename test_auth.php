<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = App\Models\User::where('email', 'aarav@mindbloom.ai')->first();
    if (!$user) {
        echo "ERROR: User not found\n";
        exit(1);
    }
    echo "User: " . $user->name . " | role=" . $user->role . "\n";
    
    $child = $user->childProfile;
    echo "Child profile: " . ($child ? $child->hero_name : 'NULL') . "\n";
    
    // Test JWT
    $token = Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
    echo "JWT Token: " . substr($token, 0, 40) . "...\n";
    
    // Check columns
    $cols = Schema::getColumnListing('users');
    echo "Users columns: " . implode(', ', $cols) . "\n";
    
    echo "\nAll OK!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "At: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
