<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InterestCategory;

class InterestCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Science',    'emoji' => '🔬', 'description' => 'Biology, chemistry, physics and experiments'],
            ['name' => 'Arts',       'emoji' => '🎨', 'description' => 'Drawing, painting, music and creativity'],
            ['name' => 'Technology', 'emoji' => '💻', 'description' => 'Computers, coding, robots and gadgets'],
            ['name' => 'Leadership', 'emoji' => '👑', 'description' => 'Teams, projects, organizing and leading'],
            ['name' => 'Sports',     'emoji' => '⚽', 'description' => 'Physical activity, games and competition'],
            ['name' => 'Nature',     'emoji' => '🌿', 'description' => 'Plants, animals, environment and outdoors'],
            ['name' => 'Stories',    'emoji' => '📚', 'description' => 'Reading, writing, storytelling and books'],
            ['name' => 'Math',       'emoji' => '🔢', 'description' => 'Numbers, patterns, logic and puzzles'],
        ];

        foreach ($categories as $cat) {
            InterestCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
