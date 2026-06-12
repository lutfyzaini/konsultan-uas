<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
 
    protected $fillable = ['username', 'email', 'password', 'role', 'status'];
 
    protected $hidden = ['password', 'remember_token'];
 
    protected $casts = ['password' => 'hashed'];
 
    // helpers
    public function isAdmin():     bool { return $this->role === 'admin'; }
    public function isExpert():    bool { return $this->role === 'expert'; }
    public function isClient():    bool { return $this->role === 'client'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
 
    // relationships
    public function profile()        { return $this->hasOne(UserProfile::class); }
    public function expertProfile()  { return $this->hasOne(ExpertProfile::class); }
    public function wallet()         { return $this->hasOne(Wallet::class); }
    public function bookings()       { return $this->hasMany(Booking::class, 'client_id'); }
    public function chatMessages()   { return $this->hasMany(ChatMessage::class, 'sender_id'); }
}
 
 
// ============================================================
// app/Models/UserProfile.php
// ============================================================
class UserProfile extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['user_id', 'name', 'phone', 'gender', 'avatar_url'];
 
    public function user() { return $this->belongsTo(User::class); }
}
