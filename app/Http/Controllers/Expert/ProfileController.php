<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $expert = $user->expertProfile()->with(['educations', 'certifications'])->first();

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        $categories = Category::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();
        $expertSkills = $expert->skills->pluck('id')->toArray();

        return view('expert.profile.edit', compact('expert', 'categories', 'skills', 'expertSkills'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $expert = $user->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        $request->validate([
            'name'             => 'required|string|max:100',
            'phone'            => 'required|string|max:20',
            'gender'           => 'required|in:male,female',
            'title'            => 'nullable|string|max:150',
            'bio'              => 'nullable|string',
            'experience_years' => 'required|integer|min:0',
            'hourly_rate'      => 'required|numeric|min:0',
            'category_id'      => 'required|exists:categories,id',
            'skills'           => 'nullable|array',
            'skills.*'         => 'exists:skills,id',
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Validasi Pendidikan
            'educations'                           => 'nullable|array',
            'educations.*.institution_name'        => 'required|string|max:255',
            'educations.*.degree'                  => 'required|string|max:255',
            'educations.*.field_of_study'          => 'required|string|max:255',
            'educations.*.start_year'              => 'required|integer|min:1900|max:' . date('Y'),
            'educations.*.end_year'                => 'nullable|integer|min:1900|max:2100',

            // Validasi Sertifikasi
            'certifications'                       => 'nullable|array',
            'certifications.*.certification_name'  => 'required|string|max:255',
            'certifications.*.issuing_organization'=> 'required|string|max:255',
            'certifications.*.issued_year'         => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        DB::transaction(function () use ($request, $user, $expert) {
            // Update UserProfile (Single Source of Truth)
            $profileData = [
                'name'   => $request->name,
                'phone'  => $request->phone,
                'gender' => $request->gender,
            ];

            // Handle Avatar Upload ke UserProfile
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'avatar_' . $expert->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Pindahkan file ke direktori public
                $file->move(public_path('images/avatars'), $filename);
                $profileData['avatar_url'] = '/images/avatars/' . $filename;
            }

            $user->profile()->update($profileData);

            // Update ExpertProfile
            $expert->update([
                'title'            => $request->title,
                'bio'              => $request->bio,
                'experience_years' => $request->experience_years,
                'hourly_rate'      => $request->hourly_rate,
                'category_id'      => $request->category_id,
            ]);

            // Sync Keterampilan
            $expert->skills()->sync($request->skills ?? []);

            // Sync Pendidikan (CRUD - delete & re-create)
            $expert->educations()->delete();
            if ($request->has('educations')) {
                foreach ($request->educations as $edu) {
                    if (!empty($edu['institution_name'])) {
                        $expert->educations()->create([
                            'institution_name' => $edu['institution_name'],
                            'degree'           => $edu['degree'],
                            'field_of_study'   => $edu['field_of_study'],
                            'start_year'       => $edu['start_year'],
                            'end_year'         => $edu['end_year'] ?? null,
                        ]);
                    }
                }
            }

            // Sync Sertifikasi (CRUD - delete & re-create)
            $expert->certifications()->delete();
            if ($request->has('certifications')) {
                foreach ($request->certifications as $cert) {
                    if (!empty($cert['certification_name'])) {
                        $expert->certifications()->create([
                            'certification_name'  => $cert['certification_name'],
                            'issuing_organization'=> $cert['issuing_organization'],
                            'issued_year'         => $cert['issued_year'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('expert.dashboard')->with('success', 'Profil berhasil diperbarui.');
    }
}
