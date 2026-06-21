# E-Konsul Project Context

Dokumen ini berisi rangkuman arsitektur, teknologi, database, alur sistem, dan kebijakan bisnis dari aplikasi **E-Konsul** (sebelumnya KonsulHub). Gunakan dokumen ini sebagai referensi utama saat melakukan modifikasi atau penambahan fitur.

---

## I. Arsitektur & Teknologi

### Tech Stack & Library

- **Core Framework**: Laravel 11.x (PHP 8.2+)
- **CSS Styling**: Tailwind CSS (via CDN di layouts)
- **Database** : MySQL
- **Real-time Chat**: AJAX/Polling (via controller room chat & status check)

### Struktur Utama Proyek

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

### Perintah Artisan Penting

- **Menjalankan Pengujian**: `php artisan test`
- **Menjalankan Migrasi**: `php artisan migrate`
- **Mengecek Status Migrasi**: `php artisan migrate:status`
- **Menjalankan Scheduler (Cron)**: `php artisan schedule:run` atau `php artisan instant:check-attendance`

---

## II. Skema Database & Migrasi Penting

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

## III. Alur Sistem Kunci

### Konsultasi Terjadwal

- Klien memilih slot ketersediaan (`availabilities`) dari profil Expert.
- Klien membayar tarif konsultasi via Wallet (status booking menjadi `confirmed`).
- Sesi konsultasi hanya dapat diakses saat waktu yang dijadwalkan tiba.
- Tambahkan validasi: Cek apakah Pakar memiliki slot booked yang akan dimulai dalam 30 menit ke depan.
  Jika ada, sistem menolak pembayaran Klien B dengan pesan: "Pakar sedang bersiap untuk sesi lain, silakan cari pakar lain."
- Jika Klien membatalkan jadwal dalam kurun waktu kurang dari 2 jam sebelum sesi dimulai, dana hanya dikembalikan 80% (20% hangus sebagai kompensasi memblokir waktu Pakar).

### Konsultasi Instan

- Klien langsung memesan ke Expert yang berstatus **Online**.
- Setelah pembayaran sukses, sistem langsung membuat Booking bertipe `instant` dan memberikan batas kehadiran 10 menit (`attendance_deadline`).
- Cronjob (`CheckInstantConsultations` / `instant:check-attendance`) berjalan setiap menit memeriksa apakah salah satu pihak tidak hadir hingga batas waktu terlewati untuk melakukan tindakan penalti (refund atau klaim dana).

### Ruang Obrolan

- Ketika salah satu pihak mengakhiri sesi(client ataupun expert), status konsultasi berubah menjadi `ended`.
- Room chat akan terkunci secara otomatis sehingga tidak ada pesan baru yang dapat dikirim.
- Riwayat chat tetap dapat dibaca dengan status "Selesai" (`done`).
- Jika durasi sesi sudah lewat batas (misalnya batas per sesi adalah 1 jam), sistem otomatis mengubah status active menjadi ended dan mencairkan uang ke Pakar.
- Sembunyikan tombol "Batalkan Pesanan" jika booking_type === 'instant'.
  Biarkan Cron Job Aturan 10 Menit (Skenario B) yang bekerja. Klien dipaksa menunggu maksimal 10 menit. Jika Pakar benar-benar tidak datang dalam 10 menit, sistem yang akan otomatis membatalkan dan me-refund uang Klien.

---

## IV. Kebijakan Bisnis & Keuangan

### Parameter Utama Pembagian Hasil

Sistem menggunakan model _Revenue Sharing_ berbasis persentase proporsional yang diambil dari tarif dasar konsultasi.

- **Platform Fee (Biaya Layanan)**: `10%` dari total tarif konsultasi.
- **Expert Net Income (Pendapatan Pakar)**: `90%` dari total tarif konsultasi.
- **Client Handling Fee (Biaya Admin Klien)**: `Rp 0` (Klien hanya membayar tarif murni yang ditetapkan oleh Pakar).

### Alur Keamanan Finansial (Sistem Escrow)

1. **Fase Pembayaran**: Saat Client melakukan _booking_, pembayaran ditampung sementara di rekening/sistem sentral (Escrow). Saldo Expert **belum** bertambah.
2. **Fase Eksekusi**: Konsultasi berjalan sesuai jadwal atau secara instan.
3. **Fase Settlement (Pencairan)**: Sistem otomatis (`AutoApproveSettlements`) membagi dana 90:10 dan memindahkannya ke _Wallet_ Expert **hanya setelah** sesi berstatus `completed`.
4. **Fase Pembatalan (Refund)**: Jika Expert gagal hadir atau sesi dibatalkan sebelum dimulai, uang dikembalikan 100% ke Klien. Platform tidak mengambil komisi dari sesi yang gagal.

### Sistem Badge (Retensi & Apresiasi Expert)

Platform menggunakan sistem _Badge_ dinamis sebagai gamifikasi untuk memotivasi Expert, bukan sistem level berjenjang yang kaku. _Badge_ dievaluasi secara berkala oleh sistem.

- 🏅 **Fast Responder**:
    - _Kriteria_: Memiliki rata-rata waktu respons di bawah 5 menit untuk _Instant Consultation_ atau _Chat_.
    - _Benefit_: Ditandai khusus di halaman pencarian saat Client memfilter "Butuh Cepat".
- ⭐ **Top Rated**:
    - _Kriteria_: Mempertahankan rating di atas 4.8 dari minimal 10 konsultasi terakhir.
    - _Benefit (Financial Reward)_: Mendapatkan "Diskon Platform Fee" sebesar `2%`. (Potongan platform fee turun dari 10% menjadi 8%, sehingga Net Income naik menjadi 92%).
- 🚀 **Rising Star**:
    - _Kriteria_: Expert baru yang mendapatkan ulasan bintang 5 pada 3 sesi pertamanya.
    - _Benefit_: Mendapat prioritas _boost_ di algoritma rekomendasi beranda selama 1 bulan pertama.

### Case pembatalan booking

- Syarat Pembatalan: Klien hanya boleh membatalkan pesanan jika statusnya masih pending_payment (belum dibayar) atau confirmed (sudah dibayar tapi sesi belum dimulai). Jika status sudah ongoing atau completed, tombol batal harus disembunyikan.

- Pelepasan Slot (Unlock): Jika pesanan bertipe Scheduled (Terjadwal), sistem wajib mengubah kembali status slot di tabel availabilities dari booked/locked menjadi available agar bisa dibeli oleh orang lain.

- Pengembalian Dana (Refund): Jika pesanan yang dibatalkan berstatus confirmed (uang sudah dipotong dari dompet klien ke Escrow), sistem wajib mengembalikan dana 100% utuh ke dompet Klien dan mencatatnya di wallet_transactions.

### Ulasan

Syarat Ulasan: Klien HANYA boleh memberikan ulasan jika status booking adalah completed.

Satu Kali Ulasan: Satu transaksi booking hanya boleh memiliki maksimal 1 ulasan (mencegah spam ulasan dari Klien).

## Kalkulasi Otomatis (Trigger): Setelah ulasan berhasil disimpan, sistem WAJIB menghitung ulang rata-rata bintang di tabel reviews dan memperbarui data

## V. Peran Aktor (Actors)

### AKTOR: ADMIN (Gatekeeper & Hakim)

- Mengelola kategori utama bidang ahli (CRUD Category).
- Mengelola daftar keahlian spesifik (CRUD Skill).
- Memverifikasi dokumen riwayat pendidikan dan sertifikat ahli (Approve/Reject status).
- Mengawasi status akun seluruh user (Aktif/Suspend) untuk menjaga keamanan platform.
- Memantau rincian biaya komisi platform 10% dan riwayat transfer dana bagi hasil ahli.
- Mengawasi log transaksi dan histori booking sesi konsultasi terjadwal/instan.
- Admin memiliki menu "Pengaturan Platform" (Settings).
Admin bisa mengubah platform_fee_percentage, nominal diskon bonus badge, atau mengubah batas waktu batal otomatis melalui tampilan antarmuka secara real-time.
- Perluasan Scope Admin: * Pakar memiliki tombol "Tarik Saldo" di dasbor mereka.
Admin memiliki menu baru: "Permintaan Pencairan" (Withdrawal Requests).
Admin bertugas memverifikasi permintaan, melakukan transfer manual ke bank Pakar, lalu mengubah status penarikan menjadi completed sambil mengunggah struk/bukti transfer. Ini mengamankan platform dari tuduhan menahan uang pengguna

### AKTOR: EXPERT (Penyedia Jasa)

- Mengatur profil kepakaran, bio, keahlian spesifik, riwayat pendidikan, dan berkas sertifikasi.
- Menentukan slot ketersediaan jadwal serta mengubah status online secara real-time.
- Melaksanakan sesi konsultasi chat/video dengan klien.
- Menerima pendapatan bersih bagi hasil 90% (atau 92% jika memiliki badge Top Rated) yang ditransfer langsung ke dompet setelah sesi selesai.
- Dapat mencairkan uang yang telah didapatkan. 


### AKTOR: CLIENT (Pengguna Jasa)

- Mencari dan menyaring expert berdasarkan kategori, keahlian spesifik, status online, dan badge.
- Melakukan pemesanan sesi konsultasi baik terjadwal maupun instan.
- Melakukan transaksi pembayaran tanpa biaya admin tambahan (Zero Hidden Fees).
- Berpartisipasi dalam room chat konsultasi dan menulis ulasan bintang atas layanan ahli.

---

## VI. Panduan Antarmuka (UI/UX)

### Standar Transparansi UI/UX & Frontend

Sistem mengedepankan prinsip _Zero Hidden Fees_. Tampilan antarmuka harus mengikuti aturan visibilitas berikut:

- **Visibilitas Client (Halaman Checkout)**: Hanya menampilkan **Subtotal Konsultasi**. Sistem tidak perlu membebani klien dengan informasi pemotongan platform di sisi ahli.
- **Visibilitas Expert (Input Tarif)**: Saat Expert menetapkan atau mengubah harga di profil mereka, UI harus secara _real-time_ menampilkan kalkulasi _Net Income_ di bawah kolom input.
- **Visibilitas Expert (Dashboard Wallet / Riwayat Transaksi)**: Setiap entri transaksi pada riwayat pendapatan wajib menampilkan rincian tiga tingkat:
    1. `Gross Revenue` (Tarif Dasar)
    2. `Deduction` (Potongan Platform Fee 10% - ditampilkan dengan warna merah/minus)
    3. `Bonus Badge` (Jika ada, misal: Top Rated Bonus +2% - warna hijau)
    4. `Net Earnings` (Total Bersih yang masuk ke dompet)

### Standar Desain Antarmuka (Palette & Tipografi)

- **Teknologi UI**: Pengembangan antarmuka **WAJIB** menggunakan _pure_ Tailwind CSS _classes_. Dilarang keras menggunakan _library_ komponen UI tambahan seperti DaisyUI agar sistem tetap ringan dan pengembang memiliki kontrol fundamental penuh terhadap tata letak.
- **Tipografi**: Menggunakan desain tipografi modern (misal dari Google Fonts seperti Inter, Roboto, atau Outfit) alih-alih default browser.

---

## VII. Instruksi Khusus Agent

> **PENTING**: Saat merespons pertanyaan dari pengguna (Client maupun Expert) terkait uang, biaya, atau pembagian hasil, Agent **WAJIB** memberikan jawaban yang lugas, transparan, dan tidak berbelit-belit.

- **Jika Client bertanya**: _"Apakah ada biaya tambahan atau admin dari aplikasi?"_
    - **Standar Respons**: "Tidak ada. Anda hanya membayar harga murni sesuai dengan tarif yang dipasang oleh Pakar di profil mereka."
- **Jika Expert bertanya**: _"Berapa potongan komisi dari aplikasi ini?"_ atau _"Bagaimana sistem gajinya?"_
    - **Standar Respons**: "Platform menerapkan sistem bagi hasil dengan persentase 90:10. Anda akan mendapatkan 90% (Pendapatan Bersih) dari setiap sesi yang selesai, sementara 10% dialokasikan sebagai biaya pemeliharaan platform. Pendapatan akan otomatis masuk ke menu Wallet Anda setelah status konsultasi selesai."
