<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Consultation extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'summary',
        'status',
        'started_at',
        'ended_at',
        // ── Kolom tambahan untuk fitur Konsultasi Instant ──
        'consultation_type',    // 'scheduled' | 'instant'
        'presence_deadline',    // started_at + 10 menit
        'absence_resolved_at',  // diisi cron setelah kehadiran diproses
    ];

    protected $casts = [
        'started_at'          => 'datetime',
        'ended_at'            => 'datetime',
        'presence_deadline'   => 'datetime',
        'absence_resolved_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('sent_at');
    }

    // ── Helper: apakah sesi ini bertipe instant? ─────────────────
    public function isInstant(): bool
    {
        return $this->consultation_type === 'instant';
    }

    // ── Helper: apakah masih dalam window kehadiran 10 menit? ────
    public function isWithinPresenceWindow(): bool
    {
        return $this->presence_deadline && now()->isBefore($this->presence_deadline);
    }

    // ── Helper: set deadline kehadiran 10 menit dari sekarang ────
    // Dipanggil saat sesi instant pertama kali dimulai (status → active)
    public function setPresenceDeadline(): void
    {
        $this->update([
            'presence_deadline' => Carbon::now()->addMinutes(
                \App\Services\InstantConsultationService::PRESENCE_WINDOW_MINUTES
            ),
        ]);
    }
}
 

