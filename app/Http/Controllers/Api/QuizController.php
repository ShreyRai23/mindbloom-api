<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Services\GamificationService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QuizController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
        private GeminiService $gemini
    ) {}

    public function index(Request $request)
    {
        $query = Quiz::where('is_active', true)->withCount('questions');
        if ($request->category) $query->where('category', $request->category);
        if ($request->difficulty) $query->where('difficulty', $request->difficulty);
        return response()->json(['quizzes' => $query->get()]);
    }

    public function show(int $id)
    {
        $quiz = Quiz::where('is_active', true)->findOrFail($id);
        
        // Generate dynamic AI questions
        $aiQuestions = $this->gemini->generateQuizQuestions($quiz->category, $quiz->difficulty, 5);
        
        $dbQuestions = [];
        foreach($aiQuestions as $index => $q) {
            $dbQuestions[] = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $q['question_text'],
                'option_a' => $q['option_a'],
                'option_b' => $q['option_b'],
                'option_c' => $q['option_c'],
                'option_d' => $q['option_d'],
                'correct_option' => $q['correct_option'],
                'explanation' => $q['explanation'] ?? '',
                'order' => $index + 1
            ]);
        }

        // Prepare quiz object with the newly generated questions
        $quizArray = $quiz->toArray();
        $quizArray['questions'] = collect($dbQuestions)->each->makeHidden('correct_option');
        
        return response()->json(['quiz' => $quizArray]);
    }

    public function submit(Request $request, int $id)
    {
        $quiz = Quiz::findOrFail($id);
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['message' => 'Child profile not found'], 403);

        $v = Validator::make($request->all(), [
            'answers'           => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.selected_option' => 'required|in:a,b,c,d',
            'time_taken_seconds' => 'nullable|integer',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $submittedAnswers = collect($request->answers)->keyBy('question_id');
        $questionIds = $submittedAnswers->keys();
        
        $questionsToGrade = QuizQuestion::whereIn('id', $questionIds)
                                        ->where('quiz_id', $quiz->id)
                                        ->get();

        $totalQuestions = $questionsToGrade->count();
        if ($totalQuestions === 0) {
            return response()->json(['message' => 'No questions found for submission'], 400);
        }

        $correctCount = 0;
        $answerRecords = [];
        $answersBreakdown = [];

        foreach ($questionsToGrade as $question) {
            $submitted = $submittedAnswers->get($question->id);
            $isCorrect = $submitted && $submitted['selected_option'] === $question->correct_option;
            if ($isCorrect) $correctCount++;
            
            $answerRecords[] = [
                'question_id'     => $question->id,
                'selected_option' => $submitted['selected_option'] ?? null,
                'is_correct'      => $isCorrect,
            ];

            $answersBreakdown[] = [
                'question'        => $question->question_text,
                'your_answer'     => $submitted['selected_option'] ?? null,
                'correct_answer'  => $question->correct_option,
                'is_correct'      => $isCorrect,
                'explanation'     => $question->explanation,
            ];
        }

        $scorePercent = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;
        $xpEarned = $this->calculateXP($quiz->xp_reward, $scorePercent);

        $attempt = QuizAttempt::create([
            'child_profile_id'  => $child->id,
            'quiz_id'           => $quiz->id,
            'score'             => $scorePercent,
            'total_questions'   => $totalQuestions,
            'correct_answers'   => $correctCount,
            'xp_earned'         => $xpEarned,
            'time_taken_seconds' => $request->time_taken_seconds,
            'completed_at'      => now(),
        ]);

        foreach ($answerRecords as $ar) {
            QuizAnswer::create(array_merge($ar, ['quiz_attempt_id' => $attempt->id]));
        }

        // Update skill score
        $this->gamification->updateSkillScore($child, $quiz->category, $scorePercent);

        // Award XP
        $xpResult = $this->gamification->awardXP($child->fresh(), $xpEarned);

        // Update streak
        $this->gamification->updateStreak($child->fresh());

        // Check achievements
        $newAchievements = $this->gamification->checkAndUnlockAchievements($child->fresh());

        // Generate AI Summary
        $aiSummary = $this->gemini->generateQuizSummary($quiz->category, $scorePercent, $answersBreakdown);

        return response()->json([
            'message'          => $this->getScoreMessage($scorePercent),
            'score'            => $scorePercent,
            'correct_answers'  => $correctCount,
            'total_questions'  => $totalQuestions,
            'xp_earned'        => $xpEarned,
            'xp_result'        => $xpResult,
            'new_achievements' => $newAchievements,
            'answers_breakdown' => $answersBreakdown,
            'ai_summary'       => $aiSummary,
        ]);
    }

    public function history(Request $request)
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['history' => []]);

        $history = QuizAttempt::with('quiz')
            ->where('child_profile_id', $child->id)
            ->orderByDesc('completed_at')
            ->take(20)->get();

        return response()->json(['history' => $history]);
    }

    private function calculateXP(int $baseXP, int $scorePercent): int
    {
        if ($scorePercent >= 90) return (int) ($baseXP * 1.5);
        if ($scorePercent >= 70) return $baseXP;
        if ($scorePercent >= 50) return (int) ($baseXP * 0.7);
        return (int) ($baseXP * 0.3);
    }

    private function getScoreMessage(int $score): string
    {
        if ($score >= 90) return '🏆 PERFECT! Incredible performance, hero!';
        if ($score >= 70) return '⭐ Great job! You crushed it!';
        if ($score >= 50) return '💪 Good effort! Keep practicing!';
        return '🎯 Keep going! Every attempt makes you stronger!';
    }
}
