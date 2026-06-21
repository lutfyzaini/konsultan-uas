<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Booking;
use App\Models\Availability;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Models\PlatformSetting;
use App\Models\Review;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoreEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private User $expertUser;
    private User $adminUser;
    private ExpertProfile $expertProfile;
    private Category $category;
    private BookingService $bookingService;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = resolve(BookingService::class);
        $this->paymentService = resolve(PaymentService::class);

        // Create Users
        $this->clientUser = User::create([
            'username' => 'client_john',
            'email' => 'john@client.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->clientUser->id, 'name' => 'John Client']);
        Wallet::create(['user_id' => $this->clientUser->id, 'balance' => 200000]);

        $this->expertUser = User::create([
            'username' => 'expert_jane',
            'email' => 'jane@expert.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->expertUser->id, 'name' => 'Jane Expert']);
        Wallet::create(['user_id' => $this->expertUser->id, 'balance' => 0]);

        $this->adminUser = User::create([
            'username' => 'admin_super',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->category = Category::create(['name' => 'Consulting']);
        $this->expertProfile = ExpertProfile::create([
            'user_id' => $this->expertUser->id,
            'category_id' => $this->category->id,
            'title' => 'Senior Consultant',
            'hourly_rate' => 100000,
            'verification_status' => 'approved',
            'is_online' => true,
        ]);
    }

    public function test_automatic_expert_rating_recalculation(): void
    {
        $booking1 = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'completed',
            'total_price' => 100000,
        ]);

        $booking2 = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'completed',
            'total_price' => 100000,
        ]);

        // Create first review
        $review1 = Review::create([
            'booking_id' => $booking1->id,
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'rating' => 4,
            'comment' => 'Good session',
        ]);

        $this->assertEquals(4.00, $this->expertProfile->fresh()->average_rating);

        // Create second review
        $review2 = Review::create([
            'booking_id' => $booking2->id,
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'rating' => 5,
            'comment' => 'Great session',
        ]);

        $this->assertEquals(4.50, $this->expertProfile->fresh()->average_rating);

        // Delete a review
        $review1->delete();
        $this->assertEquals(5.00, $this->expertProfile->fresh()->average_rating);
    }

    public function test_cancellation_refund_rules(): void
    {
        $slot = Availability::create([
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week' => 'Senin',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'booked',
        ]);

        // Case 1: Cancel > 2 hours before session
        $bookingFar = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'availability_id' => $slot->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
            'total_price' => 100000,
        ]);

        Payment::create([
            'booking_id' => $bookingFar->id,
            'invoice' => 'INV-FAR-1',
            'amount' => 100000,
            'status' => 'paid',
            'method' => 'wallet',
        ]);

        // Set DB directly
        Wallet::where('user_id', $this->clientUser->id)->update(['balance' => 0]);

        $this->bookingService->cancelBooking($bookingFar, 'client_cancelled');

        $this->assertEquals('cancelled', $bookingFar->fresh()->status);
        $this->assertEquals(100000, Wallet::where('user_id', $this->clientUser->id)->value('balance')); // 100% Refund
        $this->assertEquals(0, Wallet::where('user_id', $this->expertUser->id)->value('balance')); // No compensation

        // Case 2: Cancel < 2 hours before session
        $bookingNear = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'availability_id' => $slot->id,
            'booking_date' => now()->toDateString(),
            'start_time' => now()->addMinutes(30)->toTimeString(),
            'end_time' => now()->addMinutes(90)->toTimeString(),
            'status' => 'confirmed',
            'total_price' => 100000,
        ]);

        Payment::create([
            'booking_id' => $bookingNear->id,
            'invoice' => 'INV-NEAR-1',
            'amount' => 100000,
            'status' => 'paid',
            'method' => 'wallet',
        ]);

        Wallet::where('user_id', $this->clientUser->id)->update(['balance' => 0]);
        Wallet::where('user_id', $this->expertUser->id)->update(['balance' => 0]);

        $this->bookingService->cancelBooking($bookingNear, 'client_cancelled');

        $this->assertEquals('cancelled', $bookingNear->fresh()->status);
        $this->assertEquals(80000, Wallet::where('user_id', $this->clientUser->id)->value('balance')); // 80% Refund
        $this->assertEquals(20000, Wallet::where('user_id', $this->expertUser->id)->value('balance')); // 20% Compensation
    }

    public function test_30_minute_booking_buffer_prevention(): void
    {
        // Add one confirmed booking for expert (from 13:00 to 14:00)
        Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'status' => 'confirmed',
            'total_price' => 100000,
        ]);

        // Attempting to pay for a booking that starts at 14:15 (ends at 15:15)
        // This starts less than 30 mins after the existing ends at 14:00.
        $conflictingBooking = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '14:15',
            'end_time' => '15:15',
            'status' => 'pending_payment',
            'total_price' => 100000,
            'payment_deadline' => now()->addMinutes(15),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Pakar sedang bersiap untuk sesi lain, silakan cari pakar lain.');

        $this->paymentService->processPayment($conflictingBooking);
    }

    public function test_withdrawal_request_flow(): void
    {
        Storage::fake('public');

        // Setup expert wallet
        Wallet::where('user_id', $this->expertUser->id)->update(['balance' => 50000]);

        // 1. Submit withdrawal request
        $response = $this->actingAs($this->expertUser)->post(route('expert.withdrawals.store'), [
            'amount' => 30000,
            'bank_name' => 'BCA',
            'account_number' => '12345678',
            'account_name' => 'Jane Expert',
        ]);

        $response->assertRedirect(route('expert.withdrawals.index'));
        $this->assertEquals(20000, Wallet::where('user_id', $this->expertUser->id)->value('balance')); // Debited instantly

        $withdrawal = WithdrawalRequest::first();
        $this->assertNotNull($withdrawal);
        $this->assertEquals(30000, $withdrawal->amount);
        $this->assertEquals('pending', $withdrawal->status);

        // 2. Admin Rejects
        $responseReject = $this->actingAs($this->adminUser)->post(route('admin.withdrawals.reject', $withdrawal->id), [
            'admin_notes' => 'Wrong bank account name',
        ]);

        $responseReject->assertRedirect(route('admin.withdrawals.index'));
        $this->assertEquals('rejected', $withdrawal->fresh()->status);
        $this->assertEquals(50000, Wallet::where('user_id', $this->expertUser->id)->value('balance')); // Refunded instantly

        // 3. Re-Submit
        $this->actingAs($this->expertUser)->post(route('expert.withdrawals.store'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '12345678',
            'account_name' => 'Jane Expert',
        ]);
        
        $newWithdrawal = WithdrawalRequest::orderBy('id', 'desc')->first();

        // 4. Admin Approves
        $receiptFile = UploadedFile::fake()->image('receipt.jpg');
        $responseApprove = $this->actingAs($this->adminUser)->post(route('admin.withdrawals.approve', $newWithdrawal->id), [
            'receipt' => $receiptFile,
        ]);

        $responseApprove->assertRedirect(route('admin.withdrawals.index'));
        $this->assertEquals('completed', $newWithdrawal->fresh()->status);
        $this->assertNotNull($newWithdrawal->fresh()->receipt_path);
        Storage::disk('public')->assertExists($newWithdrawal->fresh()->receipt_path);
    }
}
