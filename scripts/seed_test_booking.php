<?php
// Script: buat test booking untuk dev
$client = App\Models\User::where('email', 'rina@konsultasi.test')->first();

$booking = App\Models\Booking::create([
    'client_id'        => $client->id,
    'expert_profile_id'=> 1,
    'booking_date'     => now()->toDateString(),
    'start_time'       => now()->toTimeString(),
    'end_time'         => now()->addHour()->toTimeString(),
    'status'           => 'pending_payment',
    'booking_type'     => 'instant',
    'total_price'      => 150000,
    'payment_deadline' => now()->addMinutes(15),
]);

echo "Booking ID: {$booking->id}\n";
echo "URL: /client/instant/{$booking->id}/payment\n";
