<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private User $expertUser1;
    private User $expertUser2;
    private ExpertProfile $expertProfile1;
    private ExpertProfile $expertProfile2;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['name' => 'IT & Software']);

        // Create Expert 1
        $this->expertUser1 = User::create([
            'username' => 'expert_1',
            'email' => 'expert1@test.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->expertUser1->id, 'name' => 'Expert Satu']);
        $this->expertProfile1 = ExpertProfile::create([
            'user_id' => $this->expertUser1->id,
            'category_id' => $this->category->id,
            'title' => 'Laravel Specialist',
            'hourly_rate' => 150000,
            'verification_status' => 'approved',
            'is_online' => true,
        ]);

        // Create Expert 2
        $this->expertUser2 = User::create([
            'username' => 'expert_2',
            'email' => 'expert2@test.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->expertUser2->id, 'name' => 'Expert Dua']);
        $this->expertProfile2 = ExpertProfile::create([
            'user_id' => $this->expertUser2->id,
            'category_id' => $this->category->id,
            'title' => 'Vue.js Specialist',
            'hourly_rate' => 120000,
            'verification_status' => 'approved',
            'is_online' => true,
        ]);

        // Create Client
        $this->clientUser = User::create([
            'username' => 'client_jerry',
            'email' => 'jerry@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        UserProfile::create(['user_id' => $this->clientUser->id, 'name' => 'Jerry Client']);
    }

    public function test_expert_can_view_their_own_reviews_and_ratings(): void
    {
        // 1. Create a booking and review for Expert 1
        $booking1 = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile1->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'completed',
            'total_price' => 150000,
        ]);

        Review::create([
            'booking_id' => $booking1->id,
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile1->id,
            'rating' => 5,
            'comment' => 'Sangat membantu dan profesional!',
        ]);

        // 2. Create a booking and review for Expert 2 (to make sure they are isolated)
        $booking2 = Booking::create([
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile2->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'completed',
            'total_price' => 120000,
        ]);

        Review::create([
            'booking_id' => $booking2->id,
            'client_id' => $this->clientUser->id,
            'expert_profile_id' => $this->expertProfile2->id,
            'rating' => 2,
            'comment' => 'Kurang responsif.',
        ]);

        // Access the reviews page as Expert 1
        $response = $this->actingAs($this->expertUser1)->get(route('expert.reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('Ulasan Klien');
        $response->assertSee('Sangat membantu dan profesional!');
        $response->assertDontSee('Kurang responsif.'); // Should not see other experts' reviews
        $response->assertSee('5.0'); // Average rating for Expert 1
        $response->assertSee('1'); // Total reviews count
    }

    public function test_guest_and_client_cannot_access_expert_reviews(): void
    {
        // Guest tries to access
        $responseGuest = $this->get(route('expert.reviews.index'));
        $responseGuest->assertRedirect(route('login'));

        // Client tries to access
        $responseClient = $this->actingAs($this->clientUser)->get(route('expert.reviews.index'));
        $responseClient->assertStatus(403);
    }
}
