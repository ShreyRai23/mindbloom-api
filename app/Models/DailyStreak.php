<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStreak extends Model
{
    protected $fillable = ['child_profile_id', 'streak_date', 'xp_earned'];
    protected $casts = ['streak_date' => 'date'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
}
