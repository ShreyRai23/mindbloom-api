<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReport extends Model
{
    protected $fillable = [
        'child_profile_id', 'summary', 'top_strength', 'learning_style',
        'personality_type', 'strengths_json', 'weaknesses_json',
        'recommendations_json', 'skill_scores_snapshot', 'report_date'
    ];
    protected $casts = [
        'strengths_json' => 'array',
        'weaknesses_json' => 'array',
        'recommendations_json' => 'array',
        'skill_scores_snapshot' => 'array',
        'report_date' => 'date',
    ];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
}
