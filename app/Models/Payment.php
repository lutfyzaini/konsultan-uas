<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'booking_id', 'invoice', 'amount', 'platform_commission',
        'expert_earnings', 'commission_rate', 'method', 'status', 'paid_at', 'settled_at',
    ];
 
    protected $casts = ['paid_at' => 'datetime', 'settled_at' => 'datetime'];
 
    public function booking() { return $this->belongsTo(Booking::class); }
}
