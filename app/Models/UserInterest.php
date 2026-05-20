<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    protected $fillable = ['child_profile_id', 'interest_category_id', 'interest_level'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
    public function category() { return $this->belongsTo(InterestCategory::class, 'interest_category_id'); }
}
