<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerRecommendation extends Model
{
    protected $fillable = ['child_profile_id', 'career_title', 'career_emoji', 'match_percentage', 'ai_reasoning', 'skills_needed', 'suggested_at'];
    protected $casts = ['suggested_at' => 'datetime', 'skills_needed' => 'array'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
}
