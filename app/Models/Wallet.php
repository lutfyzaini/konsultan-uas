<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['user_id', 'balance', 'total_earned', 'total_withdrawn'];
 
    protected $casts = ['balance' => 'decimal:2'];
 
    public function user()         { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(WalletTransaction::class); }
 
    // helpers — selalu pakai ini untuk operasi saldo
    public function debit(float $amount): void
    {
        if ($this->balance < $amount) throw new \Exception('Saldo tidak mencukupi.');
        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);
    }
 
    public function credit(float $amount): void
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
    }
}
