<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            // Daily missions
            ['title' => 'Logic Puzzle', 'emoji' => '🧩', 'description' => 'Complete 1 Logic quiz to sharpen your reasoning skills!', 'category' => 'Logic', 'xp_reward' => 120, 'type' => 'daily'],
            ['title' => 'Creativity Challenge', 'emoji' => '🎨', 'description' => 'Tackle a Creativity quiz and express your inner artist!', 'category' => 'Creativity', 'xp_reward' => 100, 'type' => 'daily'],
            ['title' => 'Memory Sprint', 'emoji' => '⚡', 'description' => 'Complete a Memory quiz and train your brain!', 'category' => 'Memory', 'xp_reward' => 90, 'type' => 'daily'],
            ['title' => 'Communication Quest', 'emoji' => '🗣️', 'description' => 'Complete a Communication quiz and level up your social skills!', 'category' => 'Communication', 'xp_reward' => 110, 'type' => 'daily'],
            ['title' => 'Leadership Training', 'emoji' => '👑', 'description' => 'Complete a Leadership quiz to awaken your inner leader!', 'category' => 'Leadership', 'xp_reward' => 130, 'type' => 'daily'],
            ['title' => 'Problem Solver', 'emoji' => '🔧', 'description' => 'Complete a Problem Solving quiz and show off your skills!', 'category' => 'Problem Solving', 'xp_reward' => 120, 'type' => 'daily'],
            ['title' => 'Focus Challenge', 'emoji' => '🎯', 'description' => 'Complete a Focus quiz — no distractions!', 'category' => 'Focus', 'xp_reward' => 100, 'type' => 'daily'],
            ['title' => 'Innovation Spark', 'emoji' => '💡', 'description' => 'Complete an Innovation quiz and think outside the box!', 'category' => 'Innovation', 'xp_reward' => 140, 'type' => 'daily'],
            ['title' => 'Daily Streak', 'emoji' => '🔥', 'description' => 'Log in and complete any quiz to keep your streak alive!', 'category' => 'General', 'xp_reward' => 20, 'type' => 'daily'],
            ['title' => 'Quick Learner', 'emoji' => '🚀', 'description' => 'Complete any 2 quizzes today for a bonus XP!', 'category' => 'General', 'xp_reward' => 200, 'type' => 'daily'],

            // Weekly missions
            ['title' => 'Brain Marathon', 'emoji' => '🏃', 'description' => 'Complete 5 quizzes this week!', 'category' => 'General', 'xp_reward' => 400, 'type' => 'weekly'],
            ['title' => 'Skill Mastery', 'emoji' => '🏆', 'description' => 'Score 80%+ on 3 different category quizzes this week!', 'category' => 'General', 'xp_reward' => 500, 'type' => 'weekly'],
            ['title' => 'Logic Lord', 'emoji' => '🧠', 'description' => 'Complete all Logic quizzes this week!', 'category' => 'Logic', 'xp_reward' => 350, 'type' => 'weekly'],
            ['title' => 'Creative Burst', 'emoji' => '🌈', 'description' => 'Complete 3 Creativity quizzes this week!', 'category' => 'Creativity', 'xp_reward' => 300, 'type' => 'weekly'],
            ['title' => 'Memory Palace', 'emoji' => '🏰', 'description' => 'Complete all Memory quizzes this week!', 'category' => 'Memory', 'xp_reward' => 350, 'type' => 'weekly'],
        ];

        foreach ($missions as $mission) {
            Mission::firstOrCreate(['title' => $mission['title']], $mission);
        }
    }
}
