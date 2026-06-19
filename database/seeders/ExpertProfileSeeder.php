<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpertProfileSeeder extends Seeder
{
    public function run(): void
    {
        // ambil ID user expert yang sudah dibuat
        $siti  = DB::table('users')->where('email', 'siti@konsultasi.test')->value('id');
        $budi  = DB::table('users')->where('email', 'budi@konsultasi.test')->value('id');
        $andi  = DB::table('users')->where('email', 'andi@konsultasi.test')->value('id');
        $rina  = DB::table('users')->where('email', 'rina_expert@konsultasi.test')->value('id');
        $dian  = DB::table('users')->where('email', 'dian@konsultasi.test')->value('id');
        $eko   = DB::table('users')->where('email', 'eko@konsultasi.test')->value('id');

        // ambil ID kategori
        $catHukum      = DB::table('categories')->where('name', 'Hukum & Legalitas')->value('id');
        $catDesain     = DB::table('categories')->where('name', 'Desain & Kreatif')->value('id');
        $catIT         = DB::table('categories')->where('name', 'Teknologi & IT')->value('id');
        $catKeuangan   = DB::table('categories')->where('name', 'Keuangan & Akuntansi')->value('id');
        $catKesehatan  = DB::table('categories')->where('name', 'Kesehatan & Medis')->value('id');
        $catPendidikan = DB::table('categories')->where('name', 'Pendidikan & Pelatihan')->value('id');

        $experts = [
            [
                'user_id'             => $siti,
                'category_id'         => $catHukum,
                'title'               => 'Konsultan Hukum & Advokat',
                'bio'                 => 'Berpengalaman 10 tahun di bidang hukum perdata dan bisnis. Menangani lebih dari 200 kasus.',
                'location'            => 'Jakarta',
                'experience_years'    => 10,
                'hourly_rate'         => 150000,
                'is_online'           => true,
                'verification_status' => 'approved', // sudah diverifikasi — muncul di katalog
                'total_sessions'      => 12,         // level Pro (10–49)
                'average_rating'      => 4.80,
                'commission_level'    => 'pro',
            ],
            [
                'user_id'             => $budi,
                'category_id'         => $catDesain,
                'title'               => 'UI/UX Designer & Brand Consultant',
                'bio'                 => 'Spesialis desain antarmuka dan identitas merek untuk startup dan UMKM.',
                'location'            => 'Bandung',
                'experience_years'    => 5,
                'hourly_rate'         => 100000,
                'is_online'           => true,
                'verification_status' => 'approved', // sudah diverifikasi
                'total_sessions'      => 3,          // level Newbie (<10)
                'average_rating'      => 4.50,
                'commission_level'    => 'newbie',
            ],
            [
                'user_id'             => $andi,
                'category_id'         => $catIT,
                'title'               => 'Full Stack Developer & IT Consultant',
                'bio'                 => 'Developer dengan keahlian Laravel, React, dan arsitektur sistem cloud.',
                'location'            => 'Surabaya',
                'experience_years'    => 7,
                'hourly_rate'         => 200000,
                'is_online'           => false,
                // sengaja pending — untuk testing fitur verifikasi admin
                'verification_status' => 'pending',
                'total_sessions'      => 0,
                'average_rating'      => 0.00,
                'commission_level'    => 'newbie',
            ],
            [
                'user_id'             => $rina,
                'category_id'         => $catKeuangan,
                'title'               => 'Konsultan Pajak & Perencana Keuangan',
                'bio'                 => 'Membantu wajib pajak perorangan maupun perusahaan dalam merencanakan kewajiban perpajakan secara efisien dan legal.',
                'location'            => 'Semarang',
                'experience_years'    => 8,
                'hourly_rate'         => 125000,
                'is_online'           => true,
                'verification_status' => 'approved',
                'total_sessions'      => 9,
                'average_rating'      => 4.60,
                'commission_level'    => 'newbie',
            ],
            [
                'user_id'             => $dian,
                'category_id'         => $catKesehatan,
                'title'               => 'Psikolog Klinis & Konsultan Mental Health',
                'bio'                 => 'Fokus membantu manajemen stres, kecemasan, hubungan interpersonal, dan pengembangan diri klien secara empati.',
                'location'            => 'Yogyakarta',
                'experience_years'    => 6,
                'hourly_rate'         => 175000,
                'is_online'           => true,
                'verification_status' => 'approved',
                'total_sessions'      => 15,
                'average_rating'      => 4.90,
                'commission_level'    => 'pro',
            ],
            [
                'user_id'             => $eko,
                'category_id'         => $catPendidikan,
                'title'               => 'Tutor Bahasa Inggris & Public Speaking Coach',
                'bio'                 => 'Membimbing profesional dan akademisi meningkatkan kemampuan komunikasi, presentasi, dan skor IELTS.',
                'location'            => 'Malang',
                'experience_years'    => 4,
                'hourly_rate'         => 80000,
                'is_online'           => true,
                'verification_status' => 'approved',
                'total_sessions'      => 22,
                'average_rating'      => 4.70,
                'commission_level'    => 'pro',
            ],
        ];

        foreach ($experts as $expert) {
            $expertId = DB::table('expert_profiles')->insertGetId(
                array_merge($expert, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // tambahkan skill ke masing-masing expert
            $this->attachSkills($expertId, $expert['category_id']);

            // tambahkan pendidikan dan sertifikasi ke masing-masing expert
            $this->attachEducationAndCertifications($expertId, $expert['user_id']);
        }

        $this->command->info('✓ ExpertProfileSeeder — 6 expert dibuat');
    }

    private function attachSkills(int $expertId, int $categoryId): void
    {
        // mapping kategori → skill yang relevan
        $catHukum      = DB::table('categories')->where('name', 'Hukum & Legalitas')->value('id');
        $catDesain     = DB::table('categories')->where('name', 'Desain & Kreatif')->value('id');
        $catIT         = DB::table('categories')->where('name', 'Teknologi & IT')->value('id');
        $catKeuangan   = DB::table('categories')->where('name', 'Keuangan & Akuntansi')->value('id');
        $catKesehatan  = DB::table('categories')->where('name', 'Kesehatan & Medis')->value('id');
        $catPendidikan = DB::table('categories')->where('name', 'Pendidikan & Pelatihan')->value('id');

        $skillMap = [
            $catHukum      => ['Hukum Perdata', 'Hukum Bisnis', 'Kontrak & Perjanjian'],
            $catDesain     => ['UI/UX Design', 'Graphic Design', 'Branding'],
            $catIT         => ['Web Development', 'Mobile Development', 'Database'],
            $catKeuangan   => ['Akuntansi', 'Pajak', 'Perencanaan Keuangan'],
            $catKesehatan  => ['Kesehatan Mental', 'Konsultasi Gizi'],
            $catPendidikan => ['Bahasa Inggris', 'Public Speaking'],
        ];

        $skillNames = $skillMap[$categoryId] ?? [];

        foreach ($skillNames as $skillName) {
            $skillId = DB::table('skills')->where('name', $skillName)->value('id');
            if ($skillId) {
                DB::table('expert_skills')->insert([
                    'expert_profile_id' => $expertId,
                    'skill_id'          => $skillId,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    private function attachEducationAndCertifications(int $expertId, int $userId): void
    {
        $siti  = DB::table('users')->where('email', 'siti@konsultasi.test')->value('id');
        $budi  = DB::table('users')->where('email', 'budi@konsultasi.test')->value('id');
        $andi  = DB::table('users')->where('email', 'andi@konsultasi.test')->value('id');
        $rina  = DB::table('users')->where('email', 'rina_expert@konsultasi.test')->value('id');
        $dian  = DB::table('users')->where('email', 'dian@konsultasi.test')->value('id');
        $eko   = DB::table('users')->where('email', 'eko@konsultasi.test')->value('id');

        if ($userId === $siti) {
            // Siti
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Universitas Indonesia',
                'degree'            => 'Sarjana Hukum (S.H.)',
                'field_of_study'    => 'Hukum Perdata',
                'start_year'        => 2012,
                'end_year'          => 2016,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'Izin Advokat PERADI',
                'issuing_organization' => 'Perhimpunan Advokat Indonesia',
                'issued_year'          => 2018,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } elseif ($userId === $budi) {
            // Budi
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Institut Teknologi Bandung',
                'degree'            => 'Magister Desain (M.Des.)',
                'field_of_study'    => 'Desain Komunikasi Visual',
                'start_year'        => 2017,
                'end_year'          => 2019,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'Google UX Design Professional Certificate',
                'issuing_organization' => 'Google',
                'issued_year'          => 2021,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } elseif ($userId === $andi) {
            // Andi
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Universitas Airlangga',
                'degree'            => 'Sarjana Komputer (S.Kom.)',
                'field_of_study'    => 'Sistem Informasi',
                'start_year'        => 2015,
                'end_year'          => 2019,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'AWS Certified Solutions Architect',
                'issuing_organization' => 'Amazon Web Services',
                'issued_year'          => 2022,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } elseif ($userId === $rina) {
            // Rina
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Universitas Diponegoro',
                'degree'            => 'Magister Manajemen (M.M.)',
                'field_of_study'    => 'Manajemen Keuangan',
                'start_year'        => 2014,
                'end_year'          => 2017,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'Certified Financial Planner (CFP)',
                'issuing_organization' => 'FPSB Indonesia',
                'issued_year'          => 2019,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } elseif ($userId === $dian) {
            // Dian
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Universitas Gadjah Mada',
                'degree'            => 'Magister Psikologi Profesi (M.Psi.)',
                'field_of_study'    => 'Psikologi Klinis',
                'start_year'        => 2016,
                'end_year'          => 2019,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'Surat Izin Praktik Psikologi (SIPP)',
                'issuing_organization' => 'HIMPSI',
                'issued_year'          => 2020,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } elseif ($userId === $eko) {
            // Eko
            DB::table('expert_education')->insert([
                'expert_profile_id' => $expertId,
                'institution_name'  => 'Universitas Negeri Malang',
                'degree'            => 'Magister Pendidikan (M.Pd.)',
                'field_of_study'    => 'Pendidikan Bahasa Inggris',
                'start_year'        => 2018,
                'end_year'          => 2021,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('expert_certifications')->insert([
                'expert_profile_id'    => $expertId,
                'certification_name'   => 'IELTS Certified Examiner',
                'issuing_organization' => 'British Council',
                'issued_year'          => 2022,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }
}