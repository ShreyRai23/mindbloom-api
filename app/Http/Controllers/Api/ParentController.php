<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\AiReport;
use App\Models\CareerRecommendation;
use App\Services\GeminiService;

class ParentController extends Controller
{
    public function __construct(private GeminiService $gemini) {}

    public function dashboard()
    {
        $parent = auth('api')->user();
        $children = $parent->children()->with([
            'user',
            'skillScores',
            'quizAttempts' => fn($q) => $q->latest()->take(7),
            'userAchievements',
        ])->get();

        $childrenData = $children->map(function ($child) {
            $latestReport = AiReport::where('child_profile_id', $child->id)->latest()->first();
            $careers = CareerRecommendation::where('child_profile_id', $child->id)
                ->orderByDesc('match_percentage')->take(3)->get();

            $skills = $child->skillScores;
            $strongest = $skills->sortByDesc('score')->first();
            $weakest   = $skills->sortBy('score')->first();

            $weeklyScores = $child->quizAttempts()
                ->whereDate('completed_at', '>=', now()->subDays(7))
                ->pluck('score')->toArray();

            $avgScore = !empty($weeklyScores) ? round(array_sum($weeklyScores) / count($weeklyScores)) : 0;

            return [
                'id'            => $child->id,
                'name'          => $child->user->name,
                'hero_name'     => $child->hero_name,
                'avatar_emoji'  => $child->avatar_emoji,
                'level'         => $child->level,
                'xp'            => $child->xp,
                'streak_count'  => $child->streak_count,
                'skill_scores'  => $skills,
                'strongest_skill' => $strongest?->category,
                'strongest_score' => $strongest?->score,
                'weakest_skill'   => $weakest?->category,
                'avg_weekly_score' => $avgScore,
                'badges_count'    => $child->userAchievements->count(),
                'quizzes_taken'   => $child->quizAttempts->count(),
                'latest_report'   => $latestReport,
                'top_careers'     => $careers,
                'recent_activity' => $child->quizAttempts->map(fn($a) => [
                    'quiz_id'     => $a->quiz_id,
                    'score'       => $a->score,
                    'xp_earned'   => $a->xp_earned,
                    'completed_at' => $a->completed_at,
                ]),
            ];
        });

        return response()->json([
            'parent'   => $parent->only(['id', 'name', 'email']),
            'children' => $childrenData,
        ]);
    }

    public function childDetail(int $childId)
    {
        $parent = auth('api')->user();
        $child = ChildProfile::where('id', $childId)
            ->where('parent_id', $parent->id)
            ->with(['user', 'skillScores', 'quizAttempts.quiz', 'userAchievements.achievement'])
            ->firstOrFail();

        $report  = AiReport::where('child_profile_id', $child->id)->latest()->first();
        $careers = CareerRecommendation::where('child_profile_id', $child->id)
            ->orderByDesc('match_percentage')->get();

        return response()->json(['child' => $child, 'report' => $report, 'careers' => $careers]);
    }

    public function linkChild(int $childId)
    {
        $parent = auth('api')->user();
        $child  = ChildProfile::findOrFail($childId);

        if ($child->parent_id && $child->parent_id !== $parent->id) {
            return response()->json(['message' => 'Child already linked to another parent.'], 403);
        }

        $child->update(['parent_id' => $parent->id]);
        return response()->json(['message' => 'Child linked successfully! 👨‍👧']);
    }
}
