<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionProgress extends Model
{
    protected $fillable = ['child_profile_id', 'mission_id', 'status', 'assigned_date', 'completed_at'];
    // status can be: 'pending' | 'ready_to_claim' | 'completed'
    protected $casts = ['completed_at' => 'datetime', 'assigned_date' => 'date'];

    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
    public function mission() { return $this->belongsTo(Mission::class); }
}
