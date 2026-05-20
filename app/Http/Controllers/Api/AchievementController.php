<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\UserAchievement;

class AchievementController extends Controller
{
    public function index()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['achievements' => []]);

        $unlockedIds = UserAchievement::where('child_profile_id', $child->id)
            ->pluck('achievement_id')->toArray();

        $unlockedAchievements = UserAchievement::with('achievement')
            ->where('child_profile_id', $child->id)
            ->orderByDesc('unlocked_at')->get();

        $allAchievements = Achievement::all()->map(fn($a) => array_merge(
            $a->toArray(),
            ['unlocked' => in_array($a->id, $unlockedIds),
             'unlocked_at' => $unlockedAchievements->firstWhere('achievement_id', $a->id)?->unlocked_at]
        ));

        return response()->json([
            'achievements'   => $allAchievements,
            'unlocked_count' => count($unlockedIds),
            'total_count'    => Achievement::count(),
        ]);
    }

    public function unlocked()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['achievements' => []]);

        $achievements = UserAchievement::with('achievement')
            ->where('child_profile_id', $child->id)
            ->orderByDesc('unlocked_at')->get();

        return response()->json(['achievements' => $achievements]);
    }
}
