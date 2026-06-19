<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExpertProfile;
use Illuminate\Http\JsonResponse;

class ExpertApiController extends Controller
{
    // ----------------------------------------------------------------
    // GET /api/categories
    // Daftar semua kategori spesialisasi
    // ----------------------------------------------------------------
    public function categories(): JsonResponse
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil.',
            'data'    => $categories,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api/experts
    // Daftar semua expert yang sudah verified (approved)
    // ----------------------------------------------------------------
    public function index(): JsonResponse
    {
        $experts = ExpertProfile::where('verification_status', 'approved')
            ->with([
                'user:id,username,email',
                'user.profile:user_id,name,avatar_url',
                'category:id,name',
                'skills:id,name',
            ])
            ->orderByDesc('average_rating')
            ->get()
            ->map(fn ($e) => $this->formatExpert($e));

        return response()->json([
            'success' => true,
            'message' => 'Daftar ahli berhasil diambil.',
            'total'   => $experts->count(),
            'data'    => $experts,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api/experts/{id}
    // Detail satu expert berdasarkan ID
    // ----------------------------------------------------------------
    public function show(int $id): JsonResponse
    {
        $expert = ExpertProfile::with([
            'user:id,username,email',
            'user.profile:user_id,name,avatar_url,gender,phone',
            'category:id,name',
            'skills:id,name',
        ])->find($id);

        if (! $expert) {
            return response()->json([
                'success' => false,
                'message' => 'Ahli tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail ahli berhasil diambil.',
            'data'    => $this->formatExpert($expert, detail: true),
        ], 200);
    }

    // ----------------------------------------------------------------
    // Helper: format output expert konsisten
    // ----------------------------------------------------------------
    private function formatExpert(ExpertProfile $expert, bool $detail = false): array
    {
        $base = [
            'id'                  => $expert->id,
            'name'                => $expert->user->profile->name ?? $expert->user->username,
            'username'            => $expert->user->username,
            'title'               => $expert->title,
            'category'            => $expert->category?->name,
            'skills'              => $expert->skills->pluck('name'),
            // 'location'            => $expert->location,
            'experience_years'    => $expert->experience_years,
            'hourly_rate'         => (int) $expert->hourly_rate,
            'hourly_rate_formatted' => 'Rp ' . number_format($expert->hourly_rate, 0, ',', '.'),
            'average_rating'      => round($expert->average_rating, 2),
            'total_sessions'      => $expert->total_sessions,
            'commission_level'    => $expert->commission_level,
            'is_online'           => (bool) $expert->is_online,
            'verification_status' => $expert->verification_status,
            'badge'               => $expert->badge,
            'avatar_url'          => $expert->user->profile->avatar_url
                                        ? url($expert->user->profile->avatar_url)
                                        : null,
        ];

        if ($detail) {
            $base['bio']   = $expert->bio;
            $base['email'] = $expert->user->email;
        }

        return $base;
    }
}
