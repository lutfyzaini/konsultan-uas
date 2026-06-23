<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExpertProfile;
use App\Models\Booking;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    // ──────────────────────────────────────────────
    // Halaman katalog / listing semua expert
    // GET /experts
    // ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ExpertProfile::with(['user.profile', 'category', 'skills'])
            ->where('verification_status', 'approved');

        // Filter: kategori
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter: hanya yang online
        if ($request->boolean('online')) {
            $query->where('is_online', true);
        }

        // Filter: pencarian nama / judul
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhereHas('user.profile', fn($q) =>
                      $q->where('name', 'like', "%{$search}%")
                  );
            });
        }

        // Sorting
        $sort = $request->get('sort', 'rating');
        match($sort) {
            'price_asc'  => $query->orderBy('hourly_rate', 'asc'),
            'price_desc' => $query->orderBy('hourly_rate', 'desc'),
            'sessions'   => $query->orderByDesc('total_sessions'),
            default      => $query->orderByDesc('average_rating'),
        };

        $experts    = $query->paginate(9)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('client.experts.index', compact('experts', 'categories'));
    }

    // ──────────────────────────────────────────────
    // Halaman detail profil expert + slot tersedia
    // GET /experts/{id}
    // ──────────────────────────────────────────────
    public function show(int $id)
    {
        $expert = ExpertProfile::with([
            'user.profile',
            'category',
            'skills',
            'reviews.client.profile',
            'educations',
            'certifications',
        ])
        ->where('verification_status', 'approved')
        ->findOrFail($id);

        // Ambil slot yang masih available, group by hari
        $slots = $expert->availabilities()
            ->where('status', 'available')
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
                ELSE 8
            END")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // Review terbaru (max 5)
        $reviews = $expert->reviews()
            ->with('client.profile')
            ->latest()
            ->limit(5)
            ->get();

        return view('client.experts.show', compact('expert', 'slots', 'reviews'));
    }
}