<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertSlotTest extends TestCase
{
    use RefreshDatabase;

    private User $expertUser;
    private ExpertProfile $expertProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat User Expert
        $this->expertUser = User::create([
            'username' => 'expert_jack',
            'email'    => 'jack@expert.com',
            'password' => bcrypt('password'),
            'role'     => 'expert',
            'status'   => 'active',
        ]);

        // 2. Buat Kategori
        $this->category = Category::create(['name' => 'IT & Software']);

        // 3. Buat ExpertProfile
        $this->expertProfile = ExpertProfile::create([
            'user_id'             => $this->expertUser->id,
            'category_id'         => $this->category->id,
            'title'               => 'Professional Consultant',
            'hourly_rate'         => 100000,
            'verification_status' => 'approved',
        ]);
    }

    public function test_expert_can_add_availability_slot(): void
    {
        $postData = [
            'day_of_week' => 'Senin',
            'start_time'  => '09:00',
            'end_time'    => '10:00',
        ];

        // Jalankan request store slot
        $response = $this->actingAs($this->expertUser)
            ->post(route('expert.slots.store'), $postData);

        // Redirect kembali ke index slot
        $response->assertRedirect(route('expert.slots.index'));
        $response->assertSessionHas('success', 'Slot jadwal berhasil ditambahkan.');

        // Pastikan terdata di DB
        $this->assertDatabaseHas('availabilities', [
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week'       => 'Senin',
            'start_time'        => '09:00',
            'end_time'          => '10:00',
            'status'            => 'available',
        ]);
    }

    public function test_expert_cannot_add_overlapping_availability_slot(): void
    {
        // 1. Buat slot pertama: Senin 09:00 - 10:00
        Availability::create([
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week'       => 'Senin',
            'start_time'        => '09:00:00',
            'end_time'          => '10:00:00',
            'status'            => 'available',
        ]);

        // 2. Request slot kedua yang overlap: Senin 09:30 - 10:30
        $overlapData = [
            'day_of_week' => 'Senin',
            'start_time'  => '09:30',
            'end_time'    => '10:30',
        ];

        $response = $this->actingAs($this->expertUser)
            ->post(route('expert.slots.store'), $overlapData);

        // Harus redirect back dengan error session
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jadwal tabrakan dengan slot yang sudah ada pada hari yang sama.');

        // Pastikan slot kedua TIDAK masuk ke database
        $this->assertDatabaseMissing('availabilities', [
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week'       => 'Senin',
            'start_time'        => '09:30:00',
            'end_time'          => '10:30:00',
        ]);
    }

    public function test_expert_can_delete_available_slot(): void
    {
        // 1. Buat slot available
        $slot = Availability::create([
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week'       => 'Selasa',
            'start_time'        => '13:00:00',
            'end_time'          => '14:00:00',
            'status'            => 'available',
        ]);

        // 2. Hapus slot
        $response = $this->actingAs($this->expertUser)
            ->delete(route('expert.slots.destroy', $slot->id));

        $response->assertRedirect(route('expert.slots.index'));
        $response->assertSessionHas('success', 'Slot jadwal berhasil dihapus.');

        // Pastikan terhapus dari DB
        $this->assertDatabaseMissing('availabilities', ['id' => $slot->id]);
    }

    public function test_expert_cannot_delete_locked_or_booked_slot(): void
    {
        // 1. Buat slot berstatus booked
        $bookedSlot = Availability::create([
            'expert_profile_id' => $this->expertProfile->id,
            'day_of_week'       => 'Rabu',
            'start_time'        => '10:00:00',
            'end_time'          => '11:00:00',
            'status'            => 'booked',
        ]);

        // 2. Hapus slot booked -> Harus ditolak
        $response = $this->actingAs($this->expertUser)
            ->delete(route('expert.slots.destroy', $bookedSlot->id));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Slot yang sudah dikunci atau dipesan tidak dapat dihapus.');

        // Pastikan slot tetap ada di DB
        $this->assertDatabaseHas('availabilities', ['id' => $bookedSlot->id, 'status' => 'booked']);
    }
}
