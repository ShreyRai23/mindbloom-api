<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['title', 'category', 'description', 'emoji', 'xp_reward', 'difficulty', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function questions() { return $this->hasMany(QuizQuestion::class)->orderBy('order'); }
    public function attempts() { return $this->hasMany(QuizAttempt::class); }
}
