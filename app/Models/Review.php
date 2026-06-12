<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Review extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['booking_id', 'client_id', 'expert_profile_id', 'rating', 'comment'];
 
    public function booking()       { return $this->belongsTo(Booking::class); }
    public function client()        { return $this->belongsTo(User::class, 'client_id'); }
    public function expertProfile() { return $this->belongsTo(ExpertProfile::class); }
}
