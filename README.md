# E-Konsul — Aplikasi Web Konsultasi Online

**E-Konsul** adalah platform web konsultasi online interaktif yang menghubungkan klien dengan pakar (expert) terverifikasi dari berbagai bidang spesialisasi (seperti hukum, IT, keuangan, desain, dll). Platform ini dirancang untuk mempermudah proses pencarian ahli, penjadwalan sesi, konsultasi real-time melalui chat, hingga pengelolaan transaksi keuangan secara aman menggunakan sistem escrow internal.

Aplikasi ini dibangun menggunakan framework **Laravel 11**, **Tailwind CSS**, arsitektur **Service Pattern**, dan database **MySQL**.

---

## 👥 Penjelasan Aktor (Roles)

Platform E-Konsul membagi pengguna ke dalam 3 aktor utama dengan hak akses dan fungsi masing-masing:

### 1. Klien (Client)
Aktor yang mencari bimbingan atau saran dari pakar.
* Dapat melakukan pengisian saldo wallet (top-up).
* Melakukan pemesanan sesi konsultasi (baik terjadwal maupun instan).
* Melakukan chat di dalam ruang konsultasi aktif.
* Memberikan ulasan dan rating ulasan kepada pakar.
* Melakukan konfirmasi penyelesaian sesi konsultasi.

### 2. Pakar (Expert)
Aktor penyedia layanan keahlian khusus yang telah melalui proses verifikasi.
* Mengelola profil portofolio (biografi, bidang keahlian, riwayat pendidikan, sertifikat).
* Mengatur tarif per jam dan ketersediaan waktu slot konsultasi.
* Mengaktifkan status online untuk konsultasi instan.
* Berinteraksi dengan klien di ruang obrolan.
* Mengajukan penarikan saldo pendapatan (withdrawal) ke rekening bank pribadi.

### 3. Administrator (Admin)
Aktor pengawas sistem dan mediator platform.
* Memverifikasi keabsahan profil dan dokumen pendaftaran pakar baru.
* Menyetujui atau menolak penarikan dana pakar dengan mengunggah bukti transfer bank.
* Mengelola sengketa konsultasi (dispute resolution) untuk memutuskan pengembalian dana (refund) atau pencairan dana.
* Memantau transaksi dan menyesuaikan konfigurasi biaya platform melalui menu Platform Settings.

---

## 🚀 Fitur-Fitur Utama

* **Katalog Pakar & Filter Pencarian**: Klien dapat memfilter pakar berdasarkan kategori spesialisasi utama serta melihat detail biografi, tarif, bintang rating, dan ulasan dari klien-klien sebelumnya.
* **Pemesanan Terjadwal (Scheduled Booking)**: Klien dapat mengunci dan membayar slot jadwal ketersediaan waktu yang dibuat pakar pada kalender profilnya.
* **Konsultasi Instan (Instant Consultation)**: Klien dapat langsung memulai sesi konsultasi saat itu juga dengan pakar yang sedang mengaktifkan status online (tanpa perlu memesan slot waktu).
* **Ruang Obrolan Interaktif (Chat Room)**: Ruang obrolan real-time dengan pesan instan. Ruang chat secara otomatis akan dikunci menjadi **Read-Only** (kotak input dan tombol kirim menghilang/dinonaktifkan) begitu sesi selesai untuk mendokumentasikan transkrip sesi dengan aman.
* **Ekspor Resume Konsultasi (PDF)**: Tombol unduh resume pada riwayat sesi konsultasi yang telah selesai, memungkinkan klien mengunduh berkas PDF rapi yang memuat Logo E-Konsul, detail identitas sesi, dan transkrip percakapan penuh.
* **Dompet Digital Terintegrasi (Internal Wallet)**: Sistem pembayaran terintegrasi dengan saldo wallet klien dan pakar, mencakup riwayat transaksi mutasi kredit/debit secara transparan.

---

## 🛠️ Fitur Pendukung

* **Sistem Verifikasi Pakar**: Alur peninjauan administrasi pakar oleh admin sebelum dapat memasang jadwal sesi.
* **Notifikasi Pengingat Sesi (Email Reminder)**: Tugas cron otomatis yang mengirim email pengingat (reminder) kepada klien dan pakar 30 menit sebelum sesi terjadwal dimulai.
* **Notifikasi Suara Chat (Sound Chime)**: Bunyi denting (chime) instan menggunakan Web Audio API di browser klien ketika menerima pesan baru dari pakar.
* **Sistem Penarikan Dana (Withdrawal Request)**: Pengajuan pencairan saldo wallet oleh pakar ke bank konvensional yang diproses secara manual oleh administrator.
* **Sistem Rating & Ulasan (Review)**: Penilaian bintang 1-5 dan ulasan tertulis dari klien yang secara otomatis memengaruhi skor reputasi rata-rata pakar (`average_rating`).
* **Sistem Sengketa (Dispute Resolution)**: Penangguhan pencairan dana jika salah satu pihak mengajukan komplain resmi, menyerahkan hak penentuan dana ke admin.
* **Platform Settings Dashboard**: Halaman khusus admin untuk mengatur variabel global seperti persentase komisi, lead time pemesanan, toleransi kehadiran, dan durasi sesi.

---

## ⚖️ Aturan Bisnis Utama (Business Rules)

### 1. Pembagian Pendapatan & Komisi Platform
* Biaya admin platform standar adalah **10%** dari tarif konsultasi pakar.
* **Top Rated Badge**: Pakar dengan rating rata-rata `>= 4.8` dan telah menyelesaikan minimal `10 sesi` mendapat diskon potongan komisi platform menjadi hanya **8%**.

### 2. Aturan Waktu & Keamanan Transaksi
* **Locking Checkout**: Saat memesan slot terjadwal, slot tersebut dikunci selama **15 menit** untuk penyelesaian pembayaran. Jika melebihi batas waktu tersebut, slot otomatis dibatalkan dan dilepas kembali ke katalog.
* **Lead Time Booking**: Pemesanan sesi terjadwal minimal harus dilakukan **2 jam sebelum** jam sesi dimulai.
* **Jeda Waktu Sesi (Buffer Time)**: Pakar wajib memiliki jeda istirahat minimal **30 menit** di antara sesi-sesi konsultasi yang telah terkonfirmasi.

### 3. Pembatalan & Pengembalian Dana (Refund)
* **Pembatalan Klien > 2 Jam**: Klien mendapatkan refund dana penuh (**100%**) ke e-wallet mereka.
* **Pembatalan Klien < 2 Jam**: Klien hanya mendapatkan refund **80%**, sedangkan **20%** disalurkan ke wallet pakar sebagai biaya kompensasi waktu.
* **Pembatalan oleh Pakar**: Dana dikembalikan **100%** ke klien, pakar mendapat catatan penalti (`penalty_count`).
* **Akun Suspended**: Jika pakar melakukan pelanggaran (mangkir/batal sepihak) sebanyak **3 kali** (`penalty_count >= 3`), akun pakar otomatis dinonaktifkan (`suspended`).

### 4. Kebijakan Kehadiran Konsultasi Instan (No-Show Deadlines)
* Batas waktu toleransi kehadiran di ruang obrolan adalah **10 menit** sejak sesi instan dibayar.
* **Klien Mangkir**: Jika pakar hadir namun klien tidak masuk obrolan hingga 10 menit berakhir, sesi dibatalkan (`client_no_show`). Uang klien hangus dan diserahkan 100% ke pakar sebagai kompensasi.
* **Pakar Mangkir**: Jika klien hadir namun pakar tidak masuk obrolan hingga 10 menit berakhir, sesi dibatalkan (`expert_no_show`). Dana klien dikembalikan utuh (100% refund) dan pakar menerima 1 catatan penalti.

### 5. Escrow Dana & Pencairan (Settlement)
* Seluruh dana pembayaran transaksi ditahan di penampungan escrow platform selama masa tunggu.
* **Pencairan Otomatis**: Dana (dikurangi fee platform) akan ditransfer otomatis ke dompet pakar dalam **24 jam setelah sesi selesai**, asalkan tidak ada ajuan komplain (dispute).
* **Pencairan Instan**: Klien dapat mengonfirmasi penyelesaian sesi obrolan secara instan di halaman detail booking (menekan tombol **"Konfirmasi Selesai"** atau **mengirim ulasan & rating**). Hal ini akan langsung melepaskan dana escrow ke wallet pakar saat itu juga.

---

## ⏰ Task Scheduling & Cron Jobs (Latar Belakang)

Platform E-Konsul memanfaatkan Laravel Task Scheduler yang dikonfigurasi pada [routes/console.php](file:///d:/laragon/www/projek/konsultasi-app/routes/console.php):

| Artisan Command | Frekuensi | Deskripsi / Alur Bisnis |
| :--- | :--- | :--- |
| `slots:release-expired` | Setiap Menit | Melepas slot `locked` yang telah melewati batas 15 menit tanpa pembayaran. |
| `instant:check-attendance` | Setiap Menit | Mengecek no-show (10 menit) sesi instan untuk memicu refund/kompensasi. |
| `payments:auto-approve` | Setiap Jam | Melakukan settlement pembayaran otomatis 24 jam setelah sesi berakhir tanpa sengketa. |
| `bookings:send-reminders` | Setiap 5 Menit | Mengirimkan email notifikasi pengingat sesi terjadwal 30 menit sebelum dimulai. |
