<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.key');
        $this->model   = config('services.gemini.model', 'gemini-3-flash-preview');
        $this->baseUrl = config('services.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models');
    }

    /**
     * Core method to call Gemini generateContent API.
     */
    private function generate(string $prompt, float $temperature = 0.7): ?string
    {
        try {
            $response = Http::timeout(60)
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => $temperature,
                        'maxOutputTokens' => 2048,
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('GeminiService error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate quiz questions for a category.
     */
    public function generateQuizQuestions(string $category, string $difficulty, int $count = 5): array
    {
        $prompt = "You are an educational quiz maker for children aged 8-16.
Generate exactly {$count} multiple choice questions for the '{$category}' skill category.
Difficulty: {$difficulty}.
The questions should be fun, educational, and age-appropriate.

Return ONLY valid JSON in this exact format (no markdown, no explanation):
{
  \"questions\": [
    {
      \"question_text\": \"What is 2 + 2?\",
      \"option_a\": \"3\",
      \"option_b\": \"4\",
      \"option_c\": \"5\",
      \"option_d\": \"6\",
      \"correct_option\": \"b\",
      \"explanation\": \"2 + 2 equals 4 because...\"
    }
  ]
}";

        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $raw = $this->generate($prompt, 0.5);
                if (!$raw) continue;

                $raw = preg_replace('/```(?:json)?\s*|\s*```/', '', $raw);
                if (preg_match('/\{.*\}/s', $raw, $matches)) {
                    $raw = $matches[0];
                }

                $decoded = json_decode(trim($raw), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['questions'])) {
                    return $decoded['questions'];
                }
                
                Log::warning("Gemini returned invalid JSON (Attempt $attempts)", ['raw' => $raw]);
            } catch (\Exception $e) {
                Log::warning("Gemini API Exception (Attempt $attempts): " . $e->getMessage());
            }
        }

        throw new \Exception("Failed to parse quiz questions after $maxAttempts attempts.");
    }

    /**
     * Generate a comprehensive summary after a quiz is completed.
     */
    public function generateQuizSummary(string $category, int $scorePercent, array $breakdown): string
    {
        $breakdownText = "";
        foreach ($breakdown as $i => $item) {
            $status = $item['is_correct'] ? "Correct" : "Incorrect";
            $breakdownText .= "Q" . ($i + 1) . ": {$item['question']} | Status: {$status}\n";
        }

        $prompt = "You are Bloomy, a friendly AI mentor for children aged 8-16.
The user just completed a quiz in the '{$category}' category and scored {$scorePercent}%.
Here is how they did on the questions:
{$breakdownText}

Write a short, encouraging summary (max 3 sentences) of their performance. 
Mention something they did well, or gently encourage them on a topic they missed.
Do not use any emojis. Do not use markdown formatting (like asterisks for bold text) or any special characters. Keep the text clean and premium.";

        $response = $this->generate($prompt, 0.7);
        if ($response) {
            $response = str_replace(['*', '_', '#', '`'], '', $response);
            return trim($response);
        }

        if ($scorePercent >= 80) return "Fantastic job on the {$category} quiz! You showed great understanding.";
        if ($scorePercent >= 50) return "Good effort on the {$category} quiz. Keep practicing to master all the concepts.";
        return "Don't worry about the score! Review the questions you missed and try the {$category} quiz again.";
    }

    /**
     * Generate career recommendations based on skill scores.
     */
    public function generateCareerRecommendations(array $skillScores, array $interests = []): array
    {
        $scoresText = collect($skillScores)->map(fn($s) => "{$s['category']}: {$s['score']}/100")->join(', ');
        $interestsText = empty($interests) ? 'Not specified' : implode(', ', $interests);

        $prompt = "You are an AI career counselor for children aged 8-16.
Based on these aptitude scores: {$scoresText}
And interests: {$interestsText}

Generate 4 career recommendations. Return ONLY valid JSON (no markdown):
{
  \"careers\": [
    {
      \"career_title\": \"Scientist\",
      \"career_emoji\": \"🔬\",
      \"match_percentage\": 92,
      \"ai_reasoning\": \"Your strong logic and focus scores make science a great fit...\",
      \"skills_needed\": [\"Curiosity\", \"Patience\", \"Analysis\"]
    }
  ]
}";

        $raw = $this->generate($prompt, 0.6);
        if (!$raw) throw new \Exception("Failed to generate career recommendations. Please try again.");

        $raw = preg_replace('/```(?:json)?\s*|\s*```/', '', $raw);
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $raw = $matches[0];
        }

        $decoded = json_decode(trim($raw), true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['careers'])) {
            return $decoded['careers'];
        }
        
        throw new \Exception("Failed to generate career recommendations. Please try again.");
    }

    /**
     * Generate a full aptitude report.
     */
    public function generateAptitudeReport(string $childName, int $age, array $skillScores, int $level, int $xp, int $totalQuizzes, array $achievements, int $streak): array
    {
        $scoresText = collect($skillScores)->map(fn($s) => "{$s['category']}: {$s['score']}/100")->join(', ');
        $achievementsText = empty($achievements) ? 'None yet' : implode(', ', $achievements);

        $prompt = "You are Bloomy, a friendly AI mentor for kids. Generate an aptitude report for {$childName}, age {$age}.
Real data for this user:
- Skill scores: {$scoresText}
- Level: {$level}
- Total XP: {$xp}
- Total Quizzes Completed: {$totalQuizzes}
- Achievements Unlocked: {$achievementsText}
- Current Daily Streak: {$streak} days

Return ONLY valid JSON (no markdown):
{
  \"summary\": \"2-3 sentence personalized summary acknowledging their specific stats, achievements, and streak...\",
  \"top_strength\": \"Logic\",
  \"learning_style\": \"Visual + Hands-on\",
  \"personality_type\": \"Curious Explorer\",
  \"strengths\": [\"Strong problem solver\", \"Consistent learner (based on streak)\"],
  \"weaknesses\": [\"Could improve communication\"],
  \"recommendations\": [\"Try story-building quests\", \"Practice group challenges\"]
}";

        $raw = $this->generate($prompt, 0.7);
        if (!$raw) throw new \Exception("Failed to generate aptitude report. Please try again.");

        $raw = preg_replace('/```(?:json)?\s*|\s*```/', '', $raw);
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $raw = $matches[0];
        }

        $decoded = json_decode(trim($raw), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        throw new \Exception("Failed to parse aptitude report. Please try again.");
    }

    /**
     * AI chat with Bloomy (mentor chatbot).
     */
    public function chat(string $childName, int $level, array $topSkills, array $history, string $userMessage): string
    {
        $skillsText = implode(', ', $topSkills);
        $historyText = collect($history)->map(fn($m) => "{$m['role']}: {$m['content']}")->join("\n");

        $prompt = "You are Bloomy, a friendly, encouraging AI mentor for {$childName} (Level {$level}).
Their top skills are: {$skillsText}.
You are child-safe, educational, fun, and supportive. Keep responses under 100 words. Do not use any emojis. Do not use markdown formatting (like asterisks for bold text) or any special characters. Keep the text clean and premium.
Never discuss harmful, adult, or inappropriate topics. If asked something off-topic, gently redirect to learning.

Conversation history:
{$historyText}

User: {$userMessage}
Bloomy:";

        $response = $this->generate($prompt, 0.8);
        if ($response) {
            $response = str_replace(['*', '_', '#', '`'], '', $response);
        }
        return $response ?? "Great question! I am thinking about that... Let us explore it together! Your skills in {$skillsText} are amazing. Keep learning!";
    }

    /**
     * Generate daily tips for a child.
     */
    public function generateDailyTips(string $childName, array $skillScores): array
    {
        $scoresText = collect($skillScores)->map(fn($s) => "{$s['category']}: {$s['score']}/100")->join(', ');

        $prompt = "You are Bloomy, an AI mentor for {$childName}. Based on scores: {$scoresText}.
Give 3 short, motivating daily tips (max 15 words each). Do not use any emojis or markdown formatting.
Return ONLY valid JSON:
{\"tips\": [\"Try a logic puzzle today to sharpen your skills.\", \"Story time boosts communication and imagination.\", \"You are doing amazing, keep up the great work.\"]}";

        $raw = $this->generate($prompt, 0.9);
        if (!$raw) return [
            "Try a logic puzzle to sharpen your skills today.",
            "Your creativity is blooming, try drawing something amazing.",
            "You are on a roll, keep up that streak."
        ];

        $raw = preg_replace('/```json\s*|\s*```/', '', $raw);
        $decoded = json_decode(trim($raw), true);
        return (json_last_error() === JSON_ERROR_NONE && isset($decoded['tips']))
            ? $decoded['tips']
            : ["Great job today, keep exploring.", "Practice makes perfect.", "You are a superstar learner."];
    }

    // Fallbacks removed in favor of strict AI generation with proper error handling.
}
