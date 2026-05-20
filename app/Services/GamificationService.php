<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\DailyStreak;
use App\Models\SkillScore;
use App\Models\SkillProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    // XP thresholds per level (cumulative)
    const XP_PER_LEVEL = 500;

    /**
     * Award XP to a child profile and handle level-up.
     */
    public function awardXP(ChildProfile $profile, int $xp): array
    {
        $oldLevel = $profile->level;
        $profile->xp += $xp;

        // Calculate new level
        $newLevel = max(1, (int) floor($profile->xp / self::XP_PER_LEVEL) + 1);
        $profile->level = $newLevel;
        $profile->save();

        $leveledUp = $newLevel > $oldLevel;

        return [
            'xp_awarded' => $xp,
            'total_xp'   => $profile->xp,
            'level'       => $profile->level,
            'leveled_up' => $leveledUp,
        ];
    }

    /**
     * Update or create today's streak entry.
     */
    public function updateStreak(ChildProfile $profile): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayStreak = DailyStreak::where('child_profile_id', $profile->id)
            ->where('streak_date', $today)->first();

        if ($todayStreak) {
            return ['streak_count' => $profile->streak_count, 'xp_earned' => 0, 'already_recorded' => true];
        }

        $yesterdayStreak = DailyStreak::where('child_profile_id', $profile->id)
            ->where('streak_date', $yesterday)->exists();

        if (!$yesterdayStreak && $profile->streak_count > 0 &&
            $profile->last_active_date && !$profile->last_active_date->isYesterday()) {
            $profile->streak_count = 0;
        }

        $profile->streak_count += 1;
        $profile->last_active_date = $today;
        $profile->save();

        $xpBonus = 20 + ($profile->streak_count >= 7 ? 30 : 0); // Bonus for 7+ day streaks

        DailyStreak::create([
            'child_profile_id' => $profile->id,
            'streak_date'      => $today,
            'xp_earned'        => $xpBonus,
        ]);

        $this->awardXP($profile, $xpBonus);

        return ['streak_count' => $profile->streak_count, 'xp_earned' => $xpBonus, 'already_recorded' => false];
    }

    /**
     * Update skill score after a quiz.
     */
    public function updateSkillScore(ChildProfile $profile, string $category, int $quizScore): void
    {
        $existing = SkillScore::where('child_profile_id', $profile->id)
            ->where('category', $category)->first();

        if ($existing) {
            // Moving average: new_score = (old_score * quizzes + new) / (quizzes + 1)
            $newScore = (int) (($existing->score * $existing->quizzes_taken + $quizScore) / ($existing->quizzes_taken + 1));
            $existing->update(['score' => $newScore, 'quizzes_taken' => $existing->quizzes_taken + 1]);
        } else {
            SkillScore::create([
                'child_profile_id' => $profile->id,
                'category'         => $category,
                'score'            => $quizScore,
                'quizzes_taken'    => 1,
            ]);
        }

        // Record progress history
        SkillProgress::create([
            'child_profile_id' => $profile->id,
            'category'         => $category,
            'score'            => $quizScore,
            'recorded_at'      => now(),
        ]);
    }

    /**
     * Check and unlock achievements for a child.
     */
    public function checkAndUnlockAchievements(ChildProfile $profile): array
    {
        $unlocked = [];
        $allAchievements = Achievement::all();
        $unlockedIds = UserAchievement::where('child_profile_id', $profile->id)
            ->pluck('achievement_id')->toArray();

        $quizCount = $profile->quizAttempts()->whereNotNull('completed_at')->count();

        foreach ($allAchievements as $achievement) {
            if (in_array($achievement->id, $unlockedIds)) continue;

            $shouldUnlock = match ($achievement->condition_type) {
                'quiz_count'   => $quizCount >= $achievement->condition_value,
                'streak'       => $profile->streak_count >= $achievement->condition_value,
                'xp'           => $profile->xp >= $achievement->condition_value,
                'level'        => $profile->level >= $achievement->condition_value,
                default        => false,
            };

            if ($shouldUnlock) {
                UserAchievement::create([
                    'child_profile_id' => $profile->id,
                    'achievement_id'   => $achievement->id,
                    'unlocked_at'      => now(),
                ]);

                if ($achievement->xp_bonus > 0) {
                    $this->awardXP($profile, $achievement->xp_bonus);
                }

                $unlocked[] = $achievement;
            }
        }

        return $unlocked;
    }

    /**
     * Calculate level from XP.
     */
    public static function getLevelFromXP(int $xp): int
    {
        return max(1, (int) floor($xp / self::XP_PER_LEVEL) + 1);
    }

    /**
     * Get XP needed for next level.
     */
    public static function getXPForNextLevel(int $level): int
    {
        return $level * self::XP_PER_LEVEL;
    }
}
