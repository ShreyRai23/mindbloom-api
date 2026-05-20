<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['child_profile_id', 'title'];
    public function childProfile() { return $this->belongsTo(ChildProfile::class); }
    public function messages() { return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at'); }
}
