<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['child_profile_id', 'quiz_id', 'score', 'total_questions', 'correct_answers', 'xp_earned', 'time_taken_seconds', 'completed_at'];
    protected $casts = ['completed_at' => 'datetime'];

    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function answers() { return $this->hasMany(QuizAnswer::class); }
}
