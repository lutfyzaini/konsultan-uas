<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['booking_id', 'type', 'summary', 'status', 'started_at', 'ended_at'];
 
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];
 
    public function booking()      { return $this->belongsTo(Booking::class); }
    public function chatMessages() { return $this->hasMany(ChatMessage::class)->orderBy('sent_at'); }
}
 

