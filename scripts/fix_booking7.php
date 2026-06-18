<?php
// Fix booking 7: set status ongoing + buat consultation
$booking = App\Models\Booking::find(7);

$booking->update([
    'status'              => 'ongoing',
    'session_started_at'  => now(),
    'attendance_deadline' => now()->addMinutes(10),
]);

$consultation = $booking->consultation()->create([
    'type'       => 'chat',
    'status'     => 'active',
    'started_at' => now(),
]);

echo "Booking status: " . $booking->fresh()->status . "\n";
echo "Consultation ID: " . $consultation->id . "\n";
echo "Done!\n";
