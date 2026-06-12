<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertProfile extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'bio', 'location',
        'experience_years', 'hourly_rate', 'is_online',
        'verification_status', 'total_sessions', 'average_rating', 'commission_level',
    ];
 
    protected $casts = [
        'is_online'   => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];
 
    // helpers
    public function isApproved(): bool { return $this->verification_status === 'approved'; }
 
    public function commissionRate(): int
    {
        return match($this->commission_level) {
            'master' => 10,
            'pro'    => 15,
            default  => 20,
        };
    }
 
    // relationships
    public function user()          { return $this->belongsTo(User::class); }
    public function category()      { return $this->belongsTo(Category::class); }
    public function skills()        { return $this->belongsToMany(Skill::class, 'expert_skills'); }
    public function availabilities(){ return $this->hasMany(Availability::class); }
    public function bookings()      { return $this->hasMany(Booking::class); }
    public function reviews()       { return $this->hasMany(Review::class); }
}

