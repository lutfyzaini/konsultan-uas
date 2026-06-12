<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['consultation_id', 'sender_id', 'message', 'type', 'is_read', 'sent_at'];
 
    protected $casts = ['sent_at' => 'datetime', 'is_read' => 'boolean'];
 
    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function sender()       { return $this->belongsTo(User::class, 'sender_id'); }
}

