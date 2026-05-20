<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillProgress extends Model
{
    protected $fillable = ['child_profile_id', 'category', 'score', 'recorded_at'];
    protected $casts = ['recorded_at' => 'datetime'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
}
