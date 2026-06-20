# E-Konsul Project Context

Dokumen ini berisi rangkuman arsitektur, teknologi, database, dan alur sistem dari aplikasi **E-Konsul** (sebelumnya KonsulHub). Gunakan dokumen ini sebagai referensi utama saat melakukan modifikasi atau penambahan fitur.

---

## 🛠️ Tech Stack & Library
- **Core Framework**: Laravel 11.x (PHP 8.2+)
- **CSS Styling**: Tailwind CSS (via CDN di layouts)
- **Database** : MySQL
- **Real-time Chat**: AJAX/Polling (via controller room chat & status check)

---

## 📂 Struktur Utama Proyek
- [app/Models](file:///d:/laragon/www/projek/konsultasi-app/app/Models):
  - `User.php` (Model Autentikasi dengan relasi profil & role client/expert)
  - `Booking.php` (Data transaksi pemesanan sesi konsultasi terjadwal/instant)
  - `Consultation.php` (Data room sesi chat konsultasi aktif)
  - `ChatMessage.php` (Pesan obrolan dalam room)
  - `Wallet.php` & `WalletTransaction.php` (Sistem dompet virtual & riwayat transaksi)
- [app/Services](file:///d:/laragon/www/projek/konsultasi-app/app/Services):
  - `BookingService.php` (Logika pembuatan booking terjadwal/instant & inisialisasi sesi)
  - `PaymentService.php` (Logika pembayaran, refund, penarikan saldo, serta autoklasifikasi no-show)
- [app/Http/Controllers](file:///d:/laragon/www/projek/konsultasi-app/app/Http/Controllers):
  - `Client/` (DashboardController, BookingController, InstantConsultationController)
  - `Expert/` (DashboardController, ConsultationController, ProfileController, ScheduleController)
- [routes/web.php](file:///d:/laragon/www/projek/konsultasi-app/routes/web.php) (Rute navigasi frontend, dashboard, room chat, dan transaksi)

---

## 💾 Skema Database & Migrasi Penting

Berikut adalah detail kolom, tipe data, dan atribut dari seluruh entitas database di dalam sistem E-Konsul:

### 1. Pengguna & Profil (Users & Profiles)
- **users** (Data akun pengguna)
  - `id` [bigint unsigned] (Primary Key)
  - `username` [varchar(50)]
  - `email` [varchar(100)] (Unique)
  - `password` [varchar(255)]
  - `role` [enum('admin','expert','client')] (default: 'client')
  - `status` [enum('active','suspended')] (default: 'active')
  - `remember_token` [varchar(100)] (nullable)
- **user_profiles** (Detail data diri client)
  - `id` [bigint unsigned] (Primary Key)
  - `user_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `name` [varchar(100)] (nullable)
  - `phone` [varchar(20)] (nullable)
  - `gender` [enum('male','female')] (nullable)
  - `avatar_url` [varchar(255)] (nullable)
- **expert_profiles** (Profil keahlian & status pakar)
  - `id` [bigint unsigned] (Primary Key)
  - `user_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `category_id` [bigint unsigned] (Foreign Key ke `categories.id`)
  - `title` [varchar(150)] (nullable)
  - `bio` [text] (nullable)
  - `location` [varchar(100)] (nullable)
  - `experience_years` [int] (default: 0)
  - `hourly_rate` [decimal(10,2)]
  - `is_online` [tinyint(1)] (default: 0)
  - `verification_status` [enum('pending','approved','rejected')] (default: 'pending')
  - `total_sessions` [int] (default: 0)
  - `average_rating` [decimal(3,2)] (default: 0.00)
  - `commission_level` [enum('newbie','pro','master')] (default: 'newbie')
  - `penalty_count` [int] (default: 0)

### 2. Pendidikan, Sertifikasi, & Keahlian Pakar
- **categories** (Kategori utama bidang ahli)
  - `id` [bigint unsigned] (Primary Key)
  - `name` [varchar(100)]
- **skills** (Keahlian spesifik)
  - `id` [bigint unsigned] (Primary Key)
  - `name` [varchar(100)]
- **expert_skills** (Relasi Many-to-Many profil pakar dengan keahlian)
  - `id` [bigint unsigned] (Primary Key)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `skill_id` [bigint unsigned] (Foreign Key ke `skills.id`)
- **expert_education** (Riwayat pendidikan formal pakar)
  - `id` [bigint unsigned] (Primary Key)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `institution_name` [varchar(255)]
  - `degree` [varchar(255)]
  - `field_of_study` [varchar(255)]
  - `start_year` [int]
  - `end_year` [int] (nullable)
- **expert_certifications** (Sertifikat kepakaran/profesional)
  - `id` [bigint unsigned] (Primary Key)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `certification_name` [varchar(255)]
  - `issuing_organization` [varchar(255)]
  - `issued_year` [int]

### 3. Ketersediaan & Transaksi Konsultasi
- **availabilities** (Slot jadwal ketersediaan pakar)
  - `id` [bigint unsigned] (Primary Key)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `day_of_week` [enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')]
  - `start_time` [time]
  - `end_time` [time]
  - `is_active` [tinyint(1)] (default: 1)
  - `status` [enum('available','locked','booked')] (default: 'available')
  - `locked_at` [timestamp] (nullable)
  - `locked_by` [bigint unsigned] (nullable)
- **bookings** (Transaksi pemesanan sesi konsultasi)
  - `id` [bigint unsigned] (Primary Key)
  - `client_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `availability_id` [bigint unsigned] (nullable - null jika booking bertipe 'instant')
  - `booking_date` [date]
  - `start_time` [time]
  - `end_time` [time]
  - `status` [enum('pending_payment','confirmed','ongoing','pending_settlement','completed','cancelled','disputed')] (default: 'pending_payment')
  - `cancel_reason` [varchar(50)] (nullable - e.g. 'expert_no_show', 'client_no_show')
  - `client_notes` [text] (nullable)
  - `total_price` [decimal(10,2)]
  - `payment_deadline` [timestamp] (nullable)
  - `session_started_at` [timestamp] (nullable)
  - `session_ended_at` [timestamp] (nullable)
  - `booking_type` [enum('scheduled','instant')] (default: 'scheduled')
  - `attendance_deadline` [timestamp] (nullable - batas 10 menit kehadiran pada sesi instant)
  - `client_joined` [tinyint(1)] (default: 0)
  - `expert_joined` [tinyint(1)] (default: 0)

### 4. Sesi Chat, Pembayaran, & Ulasan
- **consultations** (Room sesi konsultasi aktif)
  - `id` [bigint unsigned] (Primary Key)
  - `booking_id` [bigint unsigned] (Foreign Key ke `bookings.id`)
  - `type` [enum('chat','video_call')] (default: 'chat')
  - `summary` [text] (nullable - ringkasan setelah sesi berakhir)
  - `status` [enum('active','ended')] (default: 'active')
  - `started_at` [timestamp] (nullable)
  - `ended_at` [timestamp] (nullable)
- **chat_messages** (Pesan obrolan dalam room konsultasi)
  - `id` [bigint unsigned] (Primary Key)
  - `consultation_id` [bigint unsigned] (Foreign Key ke `consultations.id`)
  - `sender_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `message` [text]
  - `type` [enum('text','image','file')] (default: 'text')
  - `is_read` [tinyint(1)] (default: 0)
  - `sent_at` [timestamp] (default: CURRENT_TIMESTAMP)
- **payments** (Riwayat penagihan & fee bagi hasil platform)
  - `id` [bigint unsigned] (Primary Key)
  - `booking_id` [bigint unsigned] (Foreign Key ke `bookings.id`)
  - `invoice` [varchar(100)] (Unique)
  - `amount` [decimal(10,2)]
  - `platform_commission` [decimal(10,2)] (default: 0.00)
  - `expert_earnings` [decimal(10,2)] (default: 0.00)
  - `commission_rate` [tinyint unsigned] (default: 0)
  - `method` [enum('bank','ewallet','credit_card','wallet')]
  - `status` [enum('unpaid','paid','refunded')] (default: 'unpaid')
  - `paid_at` [timestamp] (nullable)
  - `settled_at` [timestamp] (nullable - kapan saldo dicairkan ke pakar)
- **reviews** (Ulasan bintang dari klien ke pakar)
  - `id` [bigint unsigned] (Primary Key)
  - `booking_id` [bigint unsigned] (Foreign Key ke `bookings.id`)
  - `client_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `expert_profile_id` [bigint unsigned] (Foreign Key ke `expert_profiles.id`)
  - `rating` [tinyint] (1-5)
  - `comment` [text] (nullable)

### 5. Keuangan & Dompet Virtual (Wallets)
- **wallets** (Saldo digital pengguna)
  - `id` [bigint unsigned] (Primary Key)
  - `user_id` [bigint unsigned] (Foreign Key ke `users.id`)
  - `balance` [decimal(12,2)] (default: 0.00)
  - `total_earned` [decimal(12,2)] (default: 0.00 - total pendapatan pakar)
  - `total_withdrawn` [decimal(12,2)] (default: 0.00 - total penarikan dana)
- **wallet_transactions** (Riwayat transaksi mutasi kredit & debit)
  - `id` [bigint unsigned] (Primary Key)
  - `wallet_id` [bigint unsigned] (Foreign Key ke `wallets.id`)
  - `booking_id` [bigint unsigned] (nullable - Foreign Key ke `bookings.id`)
  - `type` [enum('credit','debit')] (credit = saldo bertambah, debit = saldo berkurang)
  - `amount` [decimal(12,2)]
  - `balance_before` [decimal(12,2)]
  - `balance_after` [decimal(12,2)]
  - `description` [varchar(255)] (nullable)

---

## 🔄 Alur & Fitur Kunci
### 1. Konsultasi Terjadwal (Scheduled Booking)
- Klien memilih slot ketersediaan (`availabilities`) dari profil Expert.
- Klien membayar tarif konsultasi via Wallet (status booking menjadi `confirmed`).
- Sesi konsultasi hanya dapat diakses saat waktu yang dijadwalkan tiba.

### 2. Konsultasi Instan (Instant Consultation)
- Klien langsung memesan ke Expert yang berstatus **Online**.
- Setelah pembayaran sukses, sistem langsung membuat Booking bertipe `instant` dan memberikan batas kehadiran 10 menit (`attendance_deadline`).
- Cronjob (`CheckInstantConsultations`) berjalan setiap menit memeriksa apakah salah satu pihak tidak hadir hingga batas waktu terlewati untuk melakukan tindakan penalti (refund atau klaim dana).

### 3. Ruang Obrolan & Penutupan Otomatis (Chat Room Auto-Close)
- Ketika salah satu pihak mengakhiri sesi, status konsultasi berubah menjadi `ended`.
- Room chat akan terkunci secara otomatis sehingga tidak ada pesan baru yang dapat dikirim.
- Riwayat chat tetap dapat dibaca dengan status "Selesai" (`done`).



---

# 📋 [System Rules] Kebijakan Pembagian Hasil (Revenue Split)

Dokumen ini mendefinisikan *business rules* dan standar operasional terkait aliran dana, potongan biaya, dan transparansi antara Platform, Client, dan Expert. Agent AI **wajib** menggunakan aturan ini sebagai *Single Source of Truth* dalam menjawab atau memproses *logic* terkait keuangan aplikasi.

---

## 1. Parameter Utama Pembagian Hasil
Sistem menggunakan model *Revenue Sharing* berbasis persentase proporsional yang diambil dari tarif dasar konsultasi.

*   **Platform Fee (Biaya Layanan):** `10%` dari total tarif konsultasi.
*   **Expert Net Income (Pendapatan Pakar):** `90%` dari total tarif konsultasi.
*   **Client Handling Fee (Biaya Admin Klien):** `Rp 0` (Klien hanya membayar tarif murni yang ditetapkan oleh Pakar).

## 2. Aturan Visibilitas & Transparansi UI/UX
Sistem mengedepankan prinsip *Zero Hidden Fees*. Tampilan antarmuka harus mengikuti aturan visibilitas berikut:

*   **Visibilitas Client (Halaman Checkout):** Hanya menampilkan **Subtotal Konsultasi**. Sistem tidak perlu membebani klien dengan informasi pemotongan platform di sisi ahli.
*   **Visibilitas Expert (Input Tarif):** Saat Expert menetapkan atau mengubah harga di profil mereka, UI harus secara *real-time* menampilkan kalkulasi *Net Income* di bawah kolom input.
*   **Visibilitas Expert (Dashboard Wallet):** Setiap entri transaksi pada riwayat pendapatan wajib menampilkan rincian tiga tingkat:
    1.  `Gross Revenue` (Tarif Dasar)
    2.  `Deduction` (Potongan Platform Fee 10% - ditampilkan dengan warna merah/minus)
    3.  `Net Earnings` (Total Bersih yang masuk ke dompet)

## 3. Alur Keamanan Finansial (Sistem Escrow)
Agent harus memahami dan menjelaskan bahwa aliran uang dilindungi oleh sistem penahanan sementara.

1.  **Fase Pembayaran:** Saat Client melakukan *booking*, pembayaran ditampung sementara di rekening/sistem sentral (Escrow). Saldo Expert **belum** bertambah.
2.  **Fase Eksekusi:** Konsultasi berjalan sesuai jadwal atau secara instan.
3.  **Fase Settlement (Pencairan):** Sistem otomatis (`AutoApproveSettlements`) membagi dana 90:10 dan memindahkannya ke *Wallet* Expert **hanya setelah** sesi berstatus `completed`.
4.  **Fase Pembatalan (Refund):** Jika Expert gagal hadir atau sesi dibatalkan sebelum dimulai, uang dikembalikan 100% ke Klien. Platform tidak mengambil komisi dari sesi yang gagal.

---

## 4. 🤖 Instruksi Khusus Agent (Response Directives)
> **PENTING:** Saat merespons pertanyaan dari pengguna (Client maupun Expert) terkait uang, biaya, atau pembagian hasil, Agent **WAJIB** memberikan jawaban yang lugas, transparan, dan tidak berbelit-belit.

*   **Jika Client bertanya:** *"Apakah ada biaya tambahan atau admin dari aplikasi?"*
    *   **Standar Respons:** "Tidak ada. Anda hanya membayar harga murni sesuai dengan tarif yang dipasang oleh Pakar di profil mereka."
*   **Jika Expert bertanya:** *"Berapa potongan komisi dari aplikasi ini?"* atau *"Bagaimana sistem gajinya?"*
    *   **Standar Respons:** "Platform menerapkan sistem bagi hasil dengan persentase 90:10. Anda akan mendapatkan 90% (Pendapatan Bersih) dari setiap sesi yang selesai, sementara 10% dialokasikan sebagai biaya pemeliharaan platform. Pendapatan akan otomatis masuk ke menu Wallet Anda setelah status konsultasi selesai."


## 3. Sistem Badge (Retensi & Apresiasi Expert)
Platform menggunakan sistem *Badge* dinamis sebagai gamifikasi untuk memotivasi Expert, bukan sistem level berjenjang yang kaku. *Badge* dievaluasi secara berkala oleh sistem.

*   🏅 **Fast Responder:**
    *   *Kriteria:* Memiliki rata-rata waktu respons di bawah 5 menit untuk *Instant Consultation* atau *Chat*.
    *   *Benefit:* Ditandai khusus di halaman pencarian saat Client memfilter "Butuh Cepat".
*   ⭐ **Top Rated:**
    *   *Kriteria:* Mempertahankan rating di atas 4.8 dari minimal 10 konsultasi terakhir.
    *   *Benefit (Financial Reward):* Mendapatkan "Diskon Platform Fee" sebesar `2%`. (Potongan fee turun dari 10% menjadi 8%, sehingga Net Income naik menjadi 92%).
*   🚀 **Rising Star:**
    *   *Kriteria:* Expert baru yang mendapatkan ulasan bintang 5 pada 3 sesi pertamanya.
    *   *Benefit:* Mendapat prioritas *boost* di algoritma rekomendasi beranda selama 1 bulan pertama.

---

## 4. Standar Transparansi UI/UX & Frontend
Sistem mengedepankan prinsip *Zero Hidden Fees*. Tampilan UI harus dikembangkan dengan aturan teknis berikut:

*   **Teknologi UI:** Pengembangan antarmuka **WAJIB** menggunakan *pure* Tailwind CSS *classes*. Dilarang keras menggunakan *library* komponen UI tambahan seperti DaisyUI agar sistem tetap ringan dan pengembang memiliki kontrol fundamental penuh terhadap tata letak.
*   **Visibilitas Client:** Halaman *Checkout* hanya menampilkan total harga konsultasi. Dilarang membebani klien dengan informasi pemotongan platform.
*   **Visibilitas Expert:** Halaman *Wallet* dan *Riwayat Transaksi* wajib memecah rincian dana menjadi:
    1.  Tarif Dasar Konsultasi
    2.  Potongan Platform (10% - warna merah)
    3.  Bonus Badge (jika ada, misal: Top Rated Bonus +2% - warna hijau)
    4.  Total Bersih (Net Earnings)

## 👥 1. AKTOR: ADMIN (Gatekeeper & Hakim)
Admin bertugas menjaga kualitas platform, mengelola sengketa, dan memantau pendapatan tanpa mencampuri alur transaksi otomatis.

* **Master Data Management (CRUD):** Admin memegang kendali tunggal untuk menambah, mengubah, dan menghapus data Kategori Spesialisasi dan Keahlian (Skills). Expert dilarang membuat kategori baru secara mandiri demi integritas database.
* **Verifikasi Pakar (Gatekeeper):** Meninjau pendaftaran Expert baru yang masuk dengan `verification_status = 'pending'`. Admin wajib memverifikasi validitas berkas di tabel `expert_educations` dan `expert_certifications` sebelum memberikan persetujuan (mengubah menjadi `'approved'`).
* **Manajemen Sengketa (Dispute Resolution):** Bertindak sebagai penengah mutlak jika Client mengajukan komplain dalam masa Escrow (1x24 jam). Admin memiliki hak eksklusif untuk mengakses dan membaca histori `chat_messages`, lalu memberikan keputusan final: eksekusi **Refund ke Client** atau **Settle ke Expert**.
* **Moderasi Pengguna (Strict Technical Rule):** Admin berhak memblokir pengguna (Client/Expert) nakal. Penangguhan akun **WAJIB** dilakukan dengan mengubah kolom `status` pada tabel `users` menjadi `'suspended'` (Dilarang menggunakan/membuat properti boolean `is_active`).
* **Pemantauan Keuangan (Finance Dashboard):** Mengawasi total perputaran uang dan komisi platform (10%). Dasbor Admin **WAJIB** merujuk pada kolom `status` di tabel `payments` dengan nilai ENUM yang valid secara database saja, yaitu: `'unpaid'`, `'paid'`, atau `'refunded'`.

---
## ⚙️ Perintah Artisan Penting
- **Menjalankan Pengujian**: `php artisan test`
- **Menjalankan Migrasi**: `php artisan migrate`
- **Mengecek Status Migrasi**: `php artisan migrate:status`
- **Menjalankan Scheduler (Cron)**: `php artisan schedule:run` atau `php artisan instant:check-attendance`
