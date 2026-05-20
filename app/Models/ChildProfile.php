<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'age', 'grade', 'hero_name',
        'avatar_emoji', 'xp', 'level', 'streak_count', 'last_active_date'
    ];

    protected $appends = ['xp_to_next_level', 'level_progress_percent'];

    protected $casts = ['last_active_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function parent() { return $this->belongsTo(User::class, 'parent_id'); }
    public function quizAttempts() { return $this->hasMany(QuizAttempt::class); }
    public function skillScores() { return $this->hasMany(SkillScore::class); }
    public function skillProgress() { return $this->hasMany(SkillProgress::class); }
    public function missionProgress() { return $this->hasMany(MissionProgress::class); }
    public function userAchievements() { return $this->hasMany(UserAchievement::class); }
    public function achievements() { return $this->belongsToMany(Achievement::class, 'user_achievements')->withPivot('unlocked_at'); }
    public function chatConversations() { return $this->hasMany(ChatConversation::class); }
    public function careerRecommendations() { return $this->hasMany(CareerRecommendation::class); }
    public function aiReports() { return $this->hasMany(AiReport::class); }
    public function dailyStreaks() { return $this->hasMany(DailyStreak::class); }
    public function userInterests() { return $this->hasMany(UserInterest::class); }

    public function getXpToNextLevelAttribute(): int
    {
        return ($this->level * 500);
    }

    public function getLevelProgressPercentAttribute(): float
    {
        $needed = $this->getXpToNextLevelAttribute();
        $base = ($this->level - 1) * 500;
        $current = $this->xp - $base;
        return min(100, round(($current / max(1, $needed - $base)) * 100, 1));
    }
}
