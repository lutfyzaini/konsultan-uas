<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private User $expertUser;
    private ExpertProfile $expertProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat user admin
        $this->admin = User::create([
            'username' => 'admin_test',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // 2. Buat user client
        $this->client = User::create([
            'username' => 'client_test',
            'email'    => 'client@test.com',
            'password' => bcrypt('password'),
            'role'     => 'client',
            'status'   => 'active',
        ]);

        // 3. Buat kategori
        $category = Category::create([
            'name' => 'IT & Software',
        ]);

        // 4. Buat user expert
        $this->expertUser = User::create([
            'username' => 'expert_test',
            'email'    => 'expert@test.com',
            'password' => bcrypt('password'),
            'role'     => 'expert',
            'status'   => 'active',
        ]);

        // 5. Buat profil expert
        $this->expertProfile = ExpertProfile::create([
            'user_id'             => $this->expertUser->id,
            'category_id'         => $category->id,
            'title'               => 'Senior Laravel Developer',
            'hourly_rate'         => 150000,
            'verification_status' => 'pending',
        ]);
    }

    public function test_non_admin_cannot_access_verification_routes(): void
    {
        // Akses oleh guest (belum login) -> redirect ke login
        $response = $this->post(route('admin.verifications.approve', $this->expertProfile->id));
        $response->assertRedirect(route('login'));

        // Akses oleh client (non-admin) -> 403 Forbidden
        $response = $this->actingAs($this->client)
            ->post(route('admin.verifications.approve', $this->expertProfile->id));
        $response->assertStatus(403);

        // Akses halaman index verifikasi oleh client -> 403 Forbidden
        $response = $this->actingAs($this->client)
            ->get(route('admin.verifications.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_approve_expert(): void
    {
        // Pastikan status awal adalah pending
        $this->assertEquals('pending', $this->expertProfile->fresh()->verification_status);

        // Kirim request approve oleh admin
        $response = $this->actingAs($this->admin)
            ->post(route('admin.verifications.approve', $this->expertProfile->id));

        // Harus redirect back dengan success session
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Expert berhasil disetujui');

        // Pastikan status di database berubah menjadi approved
        $this->assertEquals('approved', $this->expertProfile->fresh()->verification_status);
    }

    public function test_admin_can_reject_expert(): void
    {
        // Pastikan status awal adalah pending
        $this->assertEquals('pending', $this->expertProfile->fresh()->verification_status);

        // Kirim request reject oleh admin
        $response = $this->actingAs($this->admin)
            ->post(route('admin.verifications.reject', $this->expertProfile->id));

        // Harus redirect back dengan success session
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Expert ditolak');

        // Pastikan status di database berubah menjadi rejected
        $this->assertEquals('rejected', $this->expertProfile->fresh()->verification_status);
    }
}
