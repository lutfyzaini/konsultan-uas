<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertProfile;

class VerificationController extends Controller
{
    public function index()
    {
        $experts = ExpertProfile::with(['user.profile', 'category', 'educations', 'certifications', 'skills'])
            ->latest()
            ->get();

        return view('admin.verifications.index', compact('experts'));
    }

    public function approve(ExpertProfile $expert)
    {
        $expert->update([
            'verification_status' => 'approved'
        ]);

        return back()->with('success', 'Expert berhasil disetujui');
    }

    public function reject(ExpertProfile $expert)
    {
        $expert->update([
            'verification_status' => 'rejected'
        ]);

        return back()->with('success', 'Expert ditolak');
    }
}