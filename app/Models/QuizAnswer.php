<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = ['quiz_attempt_id', 'question_id', 'selected_option', 'is_correct'];
    protected $casts = ['is_correct' => 'boolean'];

    public function attempt() { return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id'); }
    public function question() { return $this->belongsTo(QuizQuestion::class, 'question_id'); }
}
