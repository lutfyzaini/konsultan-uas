<?php
$b = App\Models\Booking::with('consultation')->find(7);
echo "status: " . $b->status . "\n";
echo "booking_type: " . $b->booking_type . "\n";
echo "consultation: " . ($b->consultation ? 'ID='.$b->consultation->id.' status='.$b->consultation->status : 'NULL') . "\n";
echo "client_joined: " . $b->client_joined . "\n";
echo "expert_joined: " . $b->expert_joined . "\n";
echo "attendance_deadline: " . $b->attendance_deadline . "\n";
