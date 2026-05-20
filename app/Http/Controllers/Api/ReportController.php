<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiReport;
use App\Models\CareerRecommendation;
use App\Services\GeminiService;
use App\Services\GamificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        private GeminiService $gemini,
        private GamificationService $gamification
    ) {}

    public function latest()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['report' => null]);

        $report = AiReport::where('child_profile_id', $child->id)
            ->orderByDesc('created_at')->first();

        $careers = CareerRecommendation::where('child_profile_id', $child->id)
            ->orderByDesc('suggested_at')->take(4)->get();

        return response()->json([
            'report'  => $report,
            'careers' => $careers,
            'child'   => $child->load('user'),
        ]);
    }

    public function generate(Request $request)
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['message' => 'Profile not found'], 403);

        $skillScores = $child->skillScores->map(fn($s) => [
            'category' => $s->category, 'score' => $s->score
        ])->toArray();

        if (empty($skillScores)) {
            return response()->json(['message' => 'Complete at least one quiz to generate a report!'], 400);
        }

        $childName = $child->hero_name ?? $child->user->name;
        $totalQuizzes = $child->quizAttempts()->count();
        $achievements = $child->userAchievements()->with('achievement')->get()->pluck('achievement.name')->toArray();
        $streak = $child->dailyStreak->current_streak ?? 0;

        // Generate report from Gemini
        $reportData = $this->gemini->generateAptitudeReport(
            $childName,
            $child->age ?? 12,
            $skillScores,
            $child->level,
            $child->xp,
            $totalQuizzes,
            $achievements,
            $streak
        );

        $report = AiReport::create([
            'child_profile_id'      => $child->id,
            'summary'               => $reportData['summary'],
            'top_strength'          => $reportData['top_strength'],
            'learning_style'        => $reportData['learning_style'],
            'personality_type'      => $reportData['personality_type'],
            'strengths_json'        => $reportData['strengths'],
            'weaknesses_json'       => $reportData['weaknesses'],
            'recommendations_json'  => $reportData['recommendations'],
            'skill_scores_snapshot' => $skillScores,
            'report_date'           => now()->toDateString(),
        ]);

        // Generate career recommendations
        $interests = $child->userInterests()->with('category')->get()
            ->pluck('category.name')->toArray();

        $careerData = $this->gemini->generateCareerRecommendations($skillScores, $interests);

        // Clear old recommendations and save new ones
        CareerRecommendation::where('child_profile_id', $child->id)->delete();
        foreach ($careerData as $career) {
            CareerRecommendation::create([
                'child_profile_id' => $child->id,
                'career_title'     => $career['career_title'],
                'career_emoji'     => $career['career_emoji'] ?? '🚀',
                'match_percentage' => $career['match_percentage'],
                'ai_reasoning'     => $career['ai_reasoning'],
                'skills_needed'    => $career['skills_needed'] ?? [],
                'suggested_at'     => now(),
            ]);
        }

        return response()->json([
            'message' => 'Report generated successfully! ✨',
            'report'  => $report->fresh(),
            'careers' => CareerRecommendation::where('child_profile_id', $child->id)->get(),
        ]);
    }

    public function downloadPdf(int $id)
    {
        $child = auth('api')->user()->childProfile;
        $report = AiReport::where('id', $id)
            ->where('child_profile_id', $child->id)->firstOrFail();

        $careers = CareerRecommendation::where('child_profile_id', $child->id)
            ->orderByDesc('match_percentage')->take(4)->get();

        $pdf = Pdf::loadView('reports.aptitude', [
            'report'  => $report,
            'child'   => $child->load('user'),
            'careers' => $careers,
        ]);

        $filename = 'mindbloom-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
