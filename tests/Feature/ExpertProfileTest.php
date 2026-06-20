<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpertProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $expertUser;
    private ExpertProfile $expertProfile;
    private Category $category;
    private Skill $skill1;
    private Skill $skill2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat User & UserProfile
        $this->expertUser = User::create([
            'username' => 'expert_joe',
            'email'    => 'joe@expert.com',
            'password' => bcrypt('password'),
            'role'     => 'expert',
            'status'   => 'active',
        ]);

        UserProfile::create([
            'user_id' => $this->expertUser->id,
            'name'    => 'Joe Expert',
            'phone'   => '08123456789',
            'gender'  => 'male',
        ]);

        // 2. Buat Kategori & Skill
        $this->category = Category::create(['name' => 'IT & Software']);
        $this->skill1 = Skill::create(['name' => 'Laravel']);
        $this->skill2 = Skill::create(['name' => 'React']);

        // 3. Buat ExpertProfile awal
        $this->expertProfile = ExpertProfile::create([
            'user_id'             => $this->expertUser->id,
            'category_id'         => $this->category->id,
            'title'               => 'Junior Dev',
            'hourly_rate'         => 50000,
            'experience_years'    => 1,
            'verification_status' => 'pending',
        ]);
    }

    public function test_expert_can_update_profile_and_sync_educations_certifications(): void
    {
        Storage::fake('public');

        // Pastikan relasi awal kosong
        $this->assertCount(0, $this->expertProfile->educations);
        $this->assertCount(0, $this->expertProfile->certifications);
        $this->assertCount(0, $this->expertProfile->skills);

        // Simulasi request data
        $postData = [
            'name'             => 'Joe Senior Expert',
            'phone'            => '0899999999',
            'gender'           => 'male',
            'title'            => 'Lead Laravel Engineer',
            'bio'              => 'Experienced web developer specialized in backend systems.',
            'experience_years' => 5,
            'hourly_rate'      => 200000,
            'category_id'      => $this->category->id,
            'skills'           => [$this->skill1->id, $this->skill2->id],
            
            // Berkas Pendidikan
            'educations' => [
                [
                    'institution_name' => 'Universitas Indonesia',
                    'degree'           => 'Sarjana Komputer',
                    'field_of_study'   => 'Teknik Informatika',
                    'start_year'       => 2018,
                    'end_year'         => 2022,
                ]
            ],
            
            // Berkas Sertifikasi
            'certifications' => [
                [
                    'certification_name'   => 'Certified Laravel Developer',
                    'issuing_organization' => 'Laravel LLC',
                    'issued_year'          => 2023,
                ]
            ],
        ];

        // Jalankan request update profil
        $response = $this->actingAs($this->expertUser)
            ->put(route('expert.profile.update'), $postData);

        // Harus redirect ke expert.dashboard
        $response->assertRedirect(route('expert.dashboard'));
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        // Refresh model profil ahli & user profile
        $this->expertProfile = $this->expertProfile->fresh();
        $userProfile = $this->expertUser->profile()->first();

        // 1. Assertions UserProfile
        $this->assertEquals('Joe Senior Expert', $userProfile->name);
        $this->assertEquals('0899999999', $userProfile->phone);

        // 2. Assertions ExpertProfile
        $this->assertEquals('Lead Laravel Engineer', $this->expertProfile->title);
        $this->assertEquals(200000, $this->expertProfile->hourly_rate);
        $this->assertEquals(5, $this->expertProfile->experience_years);

        // 3. Assertions Skills
        $this->assertCount(2, $this->expertProfile->skills);
        $this->assertTrue($this->expertProfile->skills->contains($this->skill1->id));

        // 4. Assertions Educations
        $this->assertCount(1, $this->expertProfile->educations);
        $edu = $this->expertProfile->educations->first();
        $this->assertEquals('Universitas Indonesia', $edu->institution_name);
        $this->assertEquals('Sarjana Komputer', $edu->degree);

        // 5. Assertions Certifications
        $this->assertCount(1, $this->expertProfile->certifications);
        $cert = $this->expertProfile->certifications->first();
        $this->assertEquals('Certified Laravel Developer', $cert->certification_name);
        $this->assertEquals('Laravel LLC', $cert->issuing_organization);
    }
}
