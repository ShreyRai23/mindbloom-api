<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = ['title', 'description', 'emoji', 'category', 'xp_reward', 'type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function progress() { return $this->hasMany(MissionProgress::class); }
}
