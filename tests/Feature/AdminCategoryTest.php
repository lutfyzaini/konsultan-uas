<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

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

        // 2. Buat kategori
        $this->category = Category::create([
            'name' => 'Kategori Uji',
        ]);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        // Kirim request hapus kategori oleh admin
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category->id));

        // Harus redirect back dengan success session
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Kategori berhasil dihapus.');

        // Kategori terhapus dari database
        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
    }

    public function test_admin_cannot_delete_category_used_by_expert(): void
    {
        // 1. Buat user expert
        $expertUser = User::create([
            'username' => 'expert_test',
            'email'    => 'expert@test.com',
            'password' => bcrypt('password'),
            'role'     => 'expert',
            'status'   => 'active',
        ]);

        // 2. Hubungkan expert dengan kategori
        ExpertProfile::create([
            'user_id'             => $expertUser->id,
            'category_id'         => $this->category->id,
            'title'               => 'Expert Developer',
            'hourly_rate'         => 100000,
            'verification_status' => 'approved',
        ]);

        // 3. Kirim request hapus kategori oleh admin -> Harus ditolak
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category->id));

        // Harus redirect back dengan error session
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Kategori gagal dihapus karena masih digunakan oleh Pakar aktif.');

        // Kategori masih ada di database
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }
}
