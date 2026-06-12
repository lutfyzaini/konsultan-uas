<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'expert_profile_id', 'day_of_week', 'start_time', 'end_time',
        'is_active', 'status', 'locked_at', 'locked_by',
    ];
 
    protected $casts = ['locked_at' => 'datetime', 'is_active' => 'boolean'];
 
    // scopes
    public function scopeAvailable($q)             { return $q->where('status', 'available'); }
    public function scopeExpiredLocked($q)          { return $q->where('status', 'locked')->where('locked_at', '<=', now()->subMinutes(15)); }
 
    // helpers
    public function lockSecondsRemaining(): int
    {
        if (!$this->locked_at) return 0;
        return max(0, (int) now()->diffInSeconds($this->locked_at->addMinutes(15), false));
    }
 
    // relationships
    public function expertProfile() { return $this->belongsTo(ExpertProfile::class); }
    public function booking()       { return $this->hasOne(Booking::class); }
}
