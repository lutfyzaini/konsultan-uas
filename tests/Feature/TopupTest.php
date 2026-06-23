<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopupTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private User $expertUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a Client
        $this->clientUser = User::create([
            'username' => 'john_client',
            'email' => 'john@client.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->clientUser->id, 'name' => 'John Client']);
        Wallet::create(['user_id' => $this->clientUser->id, 'balance' => 0]);

        // Create an Expert
        $this->expertUser = User::create([
            'username' => 'jane_expert',
            'email' => 'jane@expert.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->expertUser->id, 'name' => 'Jane Expert']);
        Wallet::create(['user_id' => $this->expertUser->id, 'balance' => 0]);
    }

    public function test_guest_cannot_access_topup_page(): void
    {
        $response = $this->get(route('client.topup.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_expert_cannot_access_topup_page(): void
    {
        $response = $this->actingAs($this->expertUser)->get(route('client.topup.index'));
        $response->assertStatus(403); // or redirect depending on middleware
    }

    public function test_client_can_access_topup_page(): void
    {
        $response = $this->actingAs($this->clientUser)->get(route('client.topup.index'));
        $response->assertStatus(200);
        $response->assertSee('Top Up Saldo E-Konsul');
    }

    public function test_client_can_submit_topup_request_and_redirect_to_payment_page(): void
    {
        $response = $this->actingAs($this->clientUser)->post(route('client.topup.store'), [
            'amount' => 150000,
            'payment_method' => 'gopay',
        ]);

        $response->assertRedirect(route('client.topup.payment', [
            'amount' => 150000,
            'method' => 'gopay',
        ]));
    }

    public function test_client_can_confirm_payment_and_credits_wallet(): void
    {
        // 1. Initial balance is 0
        $this->assertEquals(0, $this->clientUser->wallet->fresh()->balance);

        // 2. Perform mock payment
        $response = $this->actingAs($this->clientUser)->post(route('client.topup.pay'), [
            'amount' => 150000,
            'method' => 'gopay',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $response->assertSessionHas('success');

        // 3. Balance updated to 150000
        $this->assertEquals(150000, $this->clientUser->wallet->fresh()->balance);

        // 4. WalletTransaction recorded
        $transaction = WalletTransaction::first();
        $this->assertNotNull($transaction);
        $this->assertEquals($this->clientUser->wallet->id, $transaction->wallet_id);
        $this->assertEquals('credit', $transaction->type);
        $this->assertEquals(150000, $transaction->amount);
        $this->assertEquals(0, $transaction->balance_before);
        $this->assertEquals(150000, $transaction->balance_after);
        $this->assertStringContainsString('via GOPAY', $transaction->description);
    }
}
