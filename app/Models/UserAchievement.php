<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    protected $fillable = ['child_profile_id', 'achievement_id', 'unlocked_at'];
    protected $casts = ['unlocked_at' => 'datetime'];

    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
    public function achievement() { return $this->belongsTo(Achievement::class); }
}
