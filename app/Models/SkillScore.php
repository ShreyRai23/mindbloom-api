<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillScore extends Model
{
    protected $fillable = ['child_profile_id', 'category', 'score', 'quizzes_taken'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
}
