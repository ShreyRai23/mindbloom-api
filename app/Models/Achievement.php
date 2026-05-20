<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['name', 'description', 'emoji', 'rarity', 'category', 'xp_bonus', 'condition_type', 'condition_value'];
    public function userAchievements() { return $this->hasMany(UserAchievement::class); }
}
