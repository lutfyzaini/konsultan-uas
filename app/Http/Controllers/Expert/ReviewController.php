<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            abort(404, 'Profil Expert tidak ditemukan.');
        }

        // Hitung total ulasan dan rata-rata rating
        $totalReviews = Review::where('expert_profile_id', $expert->id)->count();
        $averageRating = Review::where('expert_profile_id', $expert->id)->avg('rating') ?? 0.00;

        // Tarik ulasan secara paginated dengan eager loading
        $reviews = Review::where('expert_profile_id', $expert->id)
            ->with(['booking.client.profile'])
            ->latest()
            ->paginate(10);

        return view('expert.reviews.index', compact('reviews', 'totalReviews', 'averageRating', 'expert'));
    }
}
