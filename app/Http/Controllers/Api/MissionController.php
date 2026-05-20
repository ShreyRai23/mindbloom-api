<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MissionProgress;
use App\Models\Mission;
use App\Services\GamificationService;
use Carbon\Carbon;

class MissionController extends Controller
{
    public function __construct(private GamificationService $gamification) {}

    public function today()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['missions' => []]);

        $today = Carbon::today();

        // Auto-assign 3 daily missions if not assigned yet
        $assigned = MissionProgress::where('child_profile_id', $child->id)
            ->where('assigned_date', $today)->count();

        if ($assigned === 0) {
            $dailyMissions = Mission::where('type', 'daily')->where('is_active', true)
                ->inRandomOrder()->take(3)->get();

            foreach ($dailyMissions as $mission) {
                MissionProgress::firstOrCreate([
                    'child_profile_id' => $child->id,
                    'mission_id'       => $mission->id,
                    'assigned_date'    => $today,
                ], ['status' => 'pending']);
            }
        }

        $progresses = MissionProgress::with('mission')
            ->where('child_profile_id', $child->id)
            ->where('assigned_date', $today)
            ->get();

        // For each mission, compute how many qualifying quiz attempts the child has
        $missions = $progresses->map(function ($p) use ($child) {
            $mission = $p->mission;
            $category = $mission->category;
            $title = $mission->title;
            $type = $mission->type;

            // Required count logic
            $requiredCount = 1;
            if (str_contains($title, 'Quick Learner'))  $requiredCount = 2;
            if (str_contains($title, 'Brain Marathon')) $requiredCount = 5;
            if (str_contains($title, 'Skill Mastery'))  $requiredCount = 3;
            if (str_contains($title, 'Logic Lord') || str_contains($title, 'Creative Burst') || str_contains($title, 'Memory Palace')) $requiredCount = 3;

            // Count qualifying attempts
            $query = \App\Models\QuizAttempt::where('child_profile_id', $child->id)->whereNotNull('completed_at');
            if ($type === 'daily') {
                $query->whereDate('completed_at', now()->toDateString());
            } else {
                $query->where('completed_at', '>=', now()->startOfWeek());
            }
            if ($category !== 'General') {
                $query->whereHas('quiz', fn($q) => $q->where('category', $category));
            }
            if ($title === 'Skill Mastery') {
                $progressCount = $query->with('quiz')->get()->pluck('quiz.category')->unique()->count();
            } else {
                $progressCount = $query->count();
            }

            // If mission is still pending but target is now met, auto-mark as ready_to_claim
            if ($p->status === 'pending' && $progressCount >= $requiredCount) {
                $p->update(['status' => 'ready_to_claim']);
                $p->status = 'ready_to_claim';
            }

            return [
                'id'             => $p->id,
                'mission'        => $mission,
                'status'         => $p->status,
                'completed_at'   => $p->completed_at,
                'assigned_date'  => $p->assigned_date,
                'progress_count' => min($progressCount, $requiredCount),
                'required_count' => $requiredCount,
            ];
        });

        $completed = $missions->whereIn('status', ['completed'])->count();

        return response()->json([
            'missions'  => $missions->values(),
            'completed' => $completed,
            'total'     => $missions->count(),
            'all_done'  => $completed === $missions->count() && $missions->count() > 0,
        ]);
    }

    public function claim(int $progressId)
    {
        $child = auth('api')->user()->childProfile;
        $progress = MissionProgress::with('mission')
            ->where('id', $progressId)
            ->where('child_profile_id', $child->id)
            ->firstOrFail();

        if ($progress->status === 'completed') {
            return response()->json(['message' => 'Already claimed! ✅', 'already_done' => true]);
        }

        if ($progress->status !== 'ready_to_claim') {
            return response()->json(['message' => 'Mission target not yet met!'], 400);
        }

        $progress->update(['status' => 'completed', 'completed_at' => now()]);

        $xpResult = $this->gamification->awardXP($child->fresh(), $progress->mission->xp_reward);
        $newAchievements = $this->gamification->checkAndUnlockAchievements($child->fresh());

        return response()->json([
            'message'          => "XP Claimed! +{$progress->mission->xp_reward} XP! 🎉",
            'xp_earned'        => $progress->mission->xp_reward,
            'xp_result'        => $xpResult,
            'new_achievements' => $newAchievements,
        ]);
    }

    public function all()
    {
        $missions = Mission::where('is_active', true)->get();
        return response()->json(['missions' => $missions]);
    }
}
