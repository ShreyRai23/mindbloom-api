<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ChildProfile;
use App\Models\ParentProfile;
use App\Models\SkillScore;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Demo child
        $childUser = User::firstOrCreate(['email' => 'aarav@nexaquest.ai'], [
            'name'         => 'Aarav Mehta',
            'password'     => Hash::make('password123'),
            'role'         => 'child',
            'avatar_emoji' => '🦊',
        ]);

        // Demo parent
        $parentUser = User::firstOrCreate(['email' => 'priya@nexaquest.ai'], [
            'name'         => 'Priya Mehta',
            'password'     => Hash::make('password123'),
            'role'         => 'parent',
            'avatar_emoji' => '👩',
        ]);

        ParentProfile::firstOrCreate(['user_id' => $parentUser->id]);

        $child = ChildProfile::firstOrCreate(['user_id' => $childUser->id], [
            'parent_id'    => $parentUser->id,
            'hero_name'    => 'PixelFox',
            'age'          => 12,
            'grade'        => '7th',
            'avatar_emoji' => '🦊',
            'xp'           => 2340,
            'level'        => 5,
            'streak_count' => 7,
        ]);

        // Add demo skill scores
        $skills = [
            ['category' => 'Logic',          'score' => 86, 'quizzes_taken' => 5],
            ['category' => 'Creativity',     'score' => 78, 'quizzes_taken' => 4],
            ['category' => 'Memory',         'score' => 64, 'quizzes_taken' => 3],
            ['category' => 'Communication',  'score' => 70, 'quizzes_taken' => 3],
            ['category' => 'Leadership',     'score' => 55, 'quizzes_taken' => 2],
            ['category' => 'Problem Solving','score' => 72, 'quizzes_taken' => 3],
            ['category' => 'Focus',          'score' => 68, 'quizzes_taken' => 2],
            ['category' => 'Innovation',     'score' => 75, 'quizzes_taken' => 2],
        ];

        foreach ($skills as $skill) {
            SkillScore::firstOrCreate(
                ['child_profile_id' => $child->id, 'category' => $skill['category']],
                $skill
            );
        }

        // Demo parent
        $parentUser = User::firstOrCreate(['email' => 'priya@nexaquest.ai'], [
            'name'         => 'Priya Mehta',
            'password'     => Hash::make('password123'),
            'role'         => 'parent',
            'avatar_emoji' => '👩',
        ]);

        ParentProfile::firstOrCreate(['user_id' => $parentUser->id]);

        // Link child to parent
        $child->update(['parent_id' => $parentUser->id]);

        $this->command->info("✅ Demo users created:");
        $this->command->info("   Child:  aarav@nexaquest.ai / password123");
        $this->command->info("   Parent: priya@nexaquest.ai / password123");
    }
}
