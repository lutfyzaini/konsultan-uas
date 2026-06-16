<?php

namespace App\Http\Controllers;

use App\Models\ExpertProfile;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil 4 expert terbaik berdasarkan rating
        // yang sudah approved dan is_online
        $featuredExperts = ExpertProfile::with(['user.profile', 'category'])
            ->where('verification_status', 'approved')
            ->where('is_online', true)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_sessions')
            ->limit(4)
            ->get();

        return view('home', compact('featuredExperts'));
    }
}