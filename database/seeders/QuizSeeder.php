<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $gemini = app(GeminiService::class);

        $quizzes = [
            ['category' => 'Logic', 'emoji' => '🧠', 'difficulty' => 'Easy', 'xp_reward' => 80,
             'title' => 'Logic Land: Beginner',
             'description' => 'Test your basic logical thinking with fun puzzles!'],
            ['category' => 'Logic', 'emoji' => '🧠', 'difficulty' => 'Medium', 'xp_reward' => 120,
             'title' => 'Logic Land: Intermediate',
             'description' => 'Level up your logical reasoning with tougher challenges!'],
            ['category' => 'Creativity', 'emoji' => '🎨', 'difficulty' => 'Easy', 'xp_reward' => 80,
             'title' => 'Creativity Kingdom: Art Basics',
             'description' => 'Explore your creative thinking and imagination!'],
            ['category' => 'Creativity', 'emoji' => '🎨', 'difficulty' => 'Medium', 'xp_reward' => 120,
             'title' => 'Creativity Kingdom: Design Thinking',
             'description' => 'Challenge your creative problem-solving skills!'],
            ['category' => 'Memory', 'emoji' => '🐙', 'difficulty' => 'Easy', 'xp_reward' => 60,
             'title' => 'Memory Reef: Brain Warm-Up',
             'description' => 'Train your memory with fun recall challenges!'],
            ['category' => 'Memory', 'emoji' => '🐙', 'difficulty' => 'Hard', 'xp_reward' => 160,
             'title' => 'Memory Reef: Deep Dive',
             'description' => 'Push your memory to the absolute limits!'],
            ['category' => 'Communication', 'emoji' => '🗣️', 'difficulty' => 'Easy', 'xp_reward' => 80,
             'title' => 'Word World: Speaking Skills',
             'description' => 'Level up your communication and expression!'],
            ['category' => 'Leadership', 'emoji' => '👑', 'difficulty' => 'Medium', 'xp_reward' => 140,
             'title' => 'Leadership Arena: Team Player',
             'description' => 'Discover your leadership style and team skills!'],
            ['category' => 'Problem Solving', 'emoji' => '🔧', 'difficulty' => 'Medium', 'xp_reward' => 120,
             'title' => 'Problem Galaxy: Science Lab',
             'description' => 'Solve real-world problems with scientific thinking!'],
            ['category' => 'Focus', 'emoji' => '🎯', 'difficulty' => 'Easy', 'xp_reward' => 70,
             'title' => 'Focus Forest: Concentration Zone',
             'description' => 'Train your attention and focus skills!'],
            ['category' => 'Innovation', 'emoji' => '💡', 'difficulty' => 'Medium', 'xp_reward' => 130,
             'title' => 'Innovation Isle: Future Thinker',
             'description' => 'Think like an inventor and solve future problems!'],
            ['category' => 'Problem Solving', 'emoji' => '🔧', 'difficulty' => 'Hard', 'xp_reward' => 180,
             'title' => 'Problem Galaxy: Master Engineer',
             'description' => 'Ultimate engineering and design challenges!'],
        ];

        foreach ($quizzes as $quizData) {
            // Skip if already exists
            if (Quiz::where('title', $quizData['title'])->exists()) continue;

            $quiz = Quiz::create(array_merge($quizData, ['is_active' => true]));
            $this->command->info("Creating quiz: {$quiz->title}");

            // Generate 5 questions via Gemini AI
            try {
                $questions = $gemini->generateQuizQuestions($quizData['category'], $quizData['difficulty'], 5);
                foreach ($questions as $i => $q) {
                    if (!isset($q['question_text'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_option'])) {
                        continue;
                    }
                    QuizQuestion::create([
                        'quiz_id'       => $quiz->id,
                        'question_text' => $q['question_text'],
                        'option_a'      => $q['option_a'],
                        'option_b'      => $q['option_b'],
                        'option_c'      => $q['option_c'],
                        'option_d'      => $q['option_d'],
                        'correct_option' => strtolower($q['correct_option']),
                        'explanation'   => $q['explanation'] ?? null,
                        'order'         => $i + 1,
                    ]);
                }
                $this->command->info("  → Created " . count($questions) . " questions");
                sleep(1); // Rate limit Gemini API
            } catch (\Exception $e) {
                $this->command->warn("  → Gemini failed for '{$quiz->title}': " . $e->getMessage());
                Log::warning("Quiz seeder Gemini error", ['quiz' => $quiz->title, 'error' => $e->getMessage()]);
                // Add fallback questions
                $this->addFallbackQuestions($quiz, $quizData['category']);
            }
        }
    }

    private function addFallbackQuestions(Quiz $quiz, string $category): void
    {
        $fallback = [
            ['question_text' => "Which skill best represents {$category}?", 'option_a' => 'Following rules', 'option_b' => 'Solving problems', 'option_c' => 'Memorizing', 'option_d' => 'Ignoring things', 'correct_option' => 'b', 'explanation' => "{$category} involves finding smart solutions!"],
            ['question_text' => "A good {$category} thinker always...", 'option_a' => 'Gives up easily', 'option_b' => 'Asks questions', 'option_c' => 'Copies others', 'option_d' => 'Avoids challenges', 'correct_option' => 'b', 'explanation' => "Curiosity is key in {$category}!"],
            ['question_text' => "To improve {$category} skills, you should...", 'option_a' => 'Watch TV all day', 'option_b' => 'Never try new things', 'option_c' => 'Practice regularly', 'option_d' => 'Avoid thinking', 'correct_option' => 'c', 'explanation' => "Practice makes perfect in {$category}!"],
            ['question_text' => "Which activity helps build {$category}?", 'option_a' => 'Puzzles and brain games', 'option_b' => 'Sleeping all day', 'option_c' => 'Avoiding books', 'option_d' => 'Being bored', 'correct_option' => 'a', 'explanation' => "Brain games are excellent for {$category}!"],
            ['question_text' => "Great {$category} skills help you to...", 'option_a' => 'Avoid helping others', 'option_b' => 'Think clearly and solve problems', 'option_c' => 'Always follow without thinking', 'option_d' => 'Give up on challenges', 'correct_option' => 'b', 'explanation' => "{$category} opens doors to great achievements!"],
        ];

        foreach ($fallback as $i => $q) {
            QuizQuestion::create(array_merge($q, ['quiz_id' => $quiz->id, 'order' => $i + 1]));
        }
        $this->command->info("  → Added 5 fallback questions");
    }
}
