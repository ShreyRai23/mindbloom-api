<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InterestCategorySeeder::class,
            AchievementSeeder::class,
            MissionSeeder::class,
            QuizSeeder::class,
            DemoUserSeeder::class,
        ]);
    }
}
