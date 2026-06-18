<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'client_id', 'expert_profile_id', 'availability_id',
        'booking_date', 'start_time', 'end_time', 'status',
        'booking_type', 'cancel_reason',
        'client_notes', 'total_price', 'payment_deadline',
        'attendance_deadline', 'client_joined', 'expert_joined',
        'session_started_at', 'session_ended_at',
    ];
 
    protected $casts = [
        'booking_date'      => 'date',
        'payment_deadline'  => 'datetime',
        'session_started_at'=> 'datetime',
        'session_ended_at'  => 'datetime',
        'total_price'       => 'decimal:2',
    ];
 
    // helpers
    public function isAutoApproveReady(): bool
    {
        return $this->status === 'pending_settlement'
            && $this->session_ended_at
            && $this->session_ended_at->addHours(24)->isPast();
    }
 
    // relationships
    public function client()        { return $this->belongsTo(User::class, 'client_id'); }
    public function expertProfile() { return $this->belongsTo(ExpertProfile::class); }
    public function availability()  { return $this->belongsTo(Availability::class); }
    public function payment()       { return $this->hasOne(Payment::class); }
    public function consultation()  { return $this->hasOne(Consultation::class); }
    public function review()        { return $this->hasOne(Review::class); }
    public function dispute()       { return $this->hasOne(Dispute::class); }
}
