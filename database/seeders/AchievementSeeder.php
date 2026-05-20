<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Quiz-count achievements
            ['name' => 'First Quest', 'emoji' => '🏆', 'description' => 'Complete your first quiz', 'rarity' => 'Common', 'xp_bonus' => 50, 'condition_type' => 'quiz_count', 'condition_value' => 1],
            ['name' => 'Quiz Explorer', 'emoji' => '🗺️', 'description' => 'Complete 5 quizzes', 'rarity' => 'Common', 'xp_bonus' => 100, 'condition_type' => 'quiz_count', 'condition_value' => 5],
            ['name' => 'Quiz Champion', 'emoji' => '🎯', 'description' => 'Complete 10 quizzes', 'rarity' => 'Rare', 'xp_bonus' => 200, 'condition_type' => 'quiz_count', 'condition_value' => 10],
            ['name' => 'Quiz Master', 'emoji' => '🧠', 'description' => 'Complete 25 quizzes', 'rarity' => 'Epic', 'xp_bonus' => 500, 'condition_type' => 'quiz_count', 'condition_value' => 25],
            ['name' => 'Quiz Legend', 'emoji' => '👑', 'description' => 'Complete 50 quizzes', 'rarity' => 'Legendary', 'xp_bonus' => 1000, 'condition_type' => 'quiz_count', 'condition_value' => 50],

            // Streak achievements
            ['name' => 'On Fire!', 'emoji' => '🔥', 'description' => 'Get a 3-day learning streak', 'rarity' => 'Common', 'xp_bonus' => 60, 'condition_type' => 'streak', 'condition_value' => 3],
            ['name' => 'Week Warrior', 'emoji' => '⭐', 'description' => 'Get a 7-day learning streak', 'rarity' => 'Rare', 'xp_bonus' => 150, 'condition_type' => 'streak', 'condition_value' => 7],
            ['name' => 'Streak Superstar', 'emoji' => '🌟', 'description' => 'Get a 14-day learning streak', 'rarity' => 'Epic', 'xp_bonus' => 350, 'condition_type' => 'streak', 'condition_value' => 14],
            ['name' => 'Unstoppable!', 'emoji' => '🚀', 'description' => 'Get a 30-day learning streak', 'rarity' => 'Legendary', 'xp_bonus' => 750, 'condition_type' => 'streak', 'condition_value' => 30],

            // XP achievements
            ['name' => 'XP Hunter', 'emoji' => '⚡', 'description' => 'Earn 500 XP total', 'rarity' => 'Common', 'xp_bonus' => 50, 'condition_type' => 'xp', 'condition_value' => 500],
            ['name' => 'XP Warrior', 'emoji' => '🛡️', 'description' => 'Earn 2000 XP total', 'rarity' => 'Rare', 'xp_bonus' => 200, 'condition_type' => 'xp', 'condition_value' => 2000],
            ['name' => 'XP Champion', 'emoji' => '🏅', 'description' => 'Earn 5000 XP total', 'rarity' => 'Epic', 'xp_bonus' => 500, 'condition_type' => 'xp', 'condition_value' => 5000],

            // Level achievements
            ['name' => 'Level Up!', 'emoji' => '🎮', 'description' => 'Reach Level 5', 'rarity' => 'Common', 'xp_bonus' => 100, 'condition_type' => 'level', 'condition_value' => 5],
            ['name' => 'Pro Player', 'emoji' => '🎲', 'description' => 'Reach Level 10', 'rarity' => 'Rare', 'xp_bonus' => 300, 'condition_type' => 'level', 'condition_value' => 10],
            ['name' => 'Elite Explorer', 'emoji' => '🦅', 'description' => 'Reach Level 20', 'rarity' => 'Legendary', 'xp_bonus' => 800, 'condition_type' => 'level', 'condition_value' => 20],
        ];

        foreach ($achievements as $ach) {
            Achievement::firstOrCreate(['name' => $ach['name']], $ach);
        }
    }
}
