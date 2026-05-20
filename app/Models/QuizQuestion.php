<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'explanation', 'order'];
    protected $hidden = ['correct_option']; // Hide from client during quiz

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function answers() { return $this->hasMany(QuizAnswer::class, 'question_id'); }

    // Only expose correct_option when grading
    public function withAnswer(): array
    {
        return array_merge($this->toArray(), ['correct_option' => $this->correct_option]);
    }
}
