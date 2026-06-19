<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pakar - KonsulHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-indigo-700 bg-clip-text text-transparent">KonsulHub</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Expert Panel</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('expert.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-all">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Edit Profil Pakar</h2>
            <p class="text-sm text-slate-400 mb-6">Perbarui data diri, keahlian, riwayat pendidikan, sertifikasi, dan tarif Anda.</p>

            <form action="{{ route('expert.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Row 1: Nama & Telepon -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $expert->user->profile->name) }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nomor Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $expert->user->profile->phone) }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                    </div>
                </div>

                <!-- Row 2: Jenis Kelamin & Gelar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jenis Kelamin</label>
                        <select name="gender" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                            <option value="male" {{ old('gender', $expert->user->profile->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $expert->user->profile->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Gelar / Spesialisasi Utama (Title)</label>
                        <input type="text" name="title" value="{{ old('title', $expert->title) }}" placeholder="Contoh: Ahli IT & Keamanan Siber"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium">
                    </div>
                </div>

                <!-- Row 3: Pengalaman & Tarif -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pengalaman Kerja (Tahun)</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years', $expert->experience_years) }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" min="0" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tarif Per Jam (Rp)</label>
                        <input type="number" name="hourly_rate" value="{{ old('hourly_rate', (int)$expert->hourly_rate) }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" min="0" required>
                    </div>
                </div>

                <!-- Row 4: Kategori Spesialisasi -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kategori Spesialisasi</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $expert->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Biografi / Deskripsi Diri</label>
                    <textarea name="bio" rows="4" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" placeholder="Ceritakan latar belakang Anda...">{{ old('bio', $expert->bio) }}</textarea>
                </div>

                <!-- Skills Tag Checklist -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Pilih Keterampilan (Skills)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($skills as $skill)
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer select-none transition-all">
                                <input type="checkbox" name="skills[]" value="{{ $skill->id }}" 
                                       {{ in_array($skill->id, $expertSkills) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-900 focus:ring-blue-900">
                                <span class="text-xs font-medium text-slate-600">{{ $skill->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Pendidikan Dinamis (Educations) -->
                <div class="border-t border-slate-100 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat Pendidikan</label>
                        <button type="button" onclick="addEducationRow()" class="px-3 py-1 text-xs font-semibold text-blue-900 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-all">+ Tambah</button>
                    </div>

                    <div id="education-rows" class="space-y-4">
                        @foreach($expert->educations as $index => $edu)
                            <div class="edu-row grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative">
                                <div class="sm:col-span-3">
                                    <input type="text" name="educations[{{ $index }}][institution_name]" value="{{ $edu->institution_name }}" placeholder="Nama Institusi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-3">
                                    <input type="text" name="educations[{{ $index }}][degree]" value="{{ $edu->degree }}" placeholder="Gelar (S1/S2/dll)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="text" name="educations[{{ $index }}][field_of_study]" value="{{ $edu->field_of_study }}" placeholder="Jurusan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="number" name="educations[{{ $index }}][start_year]" value="{{ $edu->start_year }}" placeholder="Tahun Mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-2 flex gap-2">
                                    <input type="number" name="educations[{{ $index }}][end_year]" value="{{ $edu->end_year }}" placeholder="Lulus (Bisa kosong)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium">
                                    <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1 text-sm">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sertifikasi Dinamis (Certifications) -->
                <div class="border-t border-slate-100 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Sertifikasi & Lisensi</label>
                        <button type="button" onclick="addCertificationRow()" class="px-3 py-1 text-xs font-semibold text-blue-900 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-all">+ Tambah</button>
                    </div>

                    <div id="certification-rows" class="space-y-4">
                        @foreach($expert->certifications as $index => $cert)
                            <div class="cert-row grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative">
                                <div class="sm:col-span-5">
                                    <input type="text" name="certifications[{{ $index }}][certification_name]" value="{{ $cert->certification_name }}" placeholder="Nama Sertifikasi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-4">
                                    <input type="text" name="certifications[{{ $index }}][issuing_organization]" value="{{ $cert->issuing_organization }}" placeholder="Lembaga Penerbit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                </div>
                                <div class="sm:col-span-3 flex gap-2">
                                    <input type="number" name="certifications[{{ $index }}][issued_year]" value="{{ $cert->issued_year }}" placeholder="Tahun Terbit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                                    <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1 text-sm">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Avatar Upload (Single Source of Truth - disimpan ke user_profiles) -->
                <div class="border-t border-slate-100 pt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto Profil (Disimpan ke Akun Utama)</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl border border-slate-200 overflow-hidden flex-shrink-0">
                            @php
                                $avatarRaw = $expert->user->profile->avatar_url ?? null;
                                $avatarUrl = $avatarRaw
                                    ? asset($avatarRaw)
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($expert->user->profile->name ?? 'E') . '&background=1e3a5f&color=fff&size=192&bold=true';
                            @endphp
                            <img id="avatar-preview" src="{{ $avatarUrl }}" alt="Avatar Preview" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)"
                                   class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1">Format JPG, JPEG, PNG. Maksimal 2MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-slate-100 pt-6 flex justify-end gap-3">
                    <a href="{{ route('expert.dashboard') }}" class="px-6 py-3 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all">Batal</a>
                    <button type="submit" class="px-6 py-3 bg-blue-900 hover:bg-indigo-900 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all">Simpan Perubahan</button>
                </div>

            </form>
        </div>

    </div>

    <script>
        let eduIndex = {{ $expert->educations->count() }};
        let certIndex = {{ $expert->certifications->count() }};

        function addEducationRow() {
            const container = document.getElementById('education-rows');
            const html = `
                <div class="edu-row grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative">
                    <div class="sm:col-span-3">
                        <input type="text" name="educations[${eduIndex}][institution_name]" placeholder="Nama Institusi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-3">
                        <input type="text" name="educations[${eduIndex}][degree]" placeholder="Gelar (S1/S2/dll)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-2">
                        <input type="text" name="educations[${eduIndex}][field_of_study]" placeholder="Jurusan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" name="educations[${eduIndex}][start_year]" placeholder="Tahun Mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <input type="number" name="educations[${eduIndex}][end_year]" placeholder="Lulus (Bisa kosong)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium">
                        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1 text-sm">✕</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            eduIndex++;
        }

        function addCertificationRow() {
            const container = document.getElementById('certification-rows');
            const html = `
                <div class="cert-row grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative">
                    <div class="sm:col-span-5">
                        <input type="text" name="certifications[${certIndex}][certification_name]" placeholder="Nama Sertifikasi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-4">
                        <input type="text" name="certifications[${certIndex}][issuing_organization]" placeholder="Lembaga Penerbit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                    </div>
                    <div class="sm:col-span-3 flex gap-2">
                        <input type="number" name="certifications[${certIndex}][issued_year]" placeholder="Tahun Terbit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium" required>
                        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1 text-sm">✕</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            certIndex++;
        }

        function removeRow(btn) {
            btn.closest('.edu-row, .cert-row').remove();
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('avatar-preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
