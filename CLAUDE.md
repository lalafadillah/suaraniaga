# SuaraNiaga: konteks proyek

Platform e-commerce inklusif dua arah berbasis suara untuk penyandang tunanetra.
Hackathon CR YOUTH 2026, 12 jam, tim 3 orang. Pengguna utama memakai screen reader
(NVDA, TalkBack, VoiceOver). Aksesibilitas adalah dasar rancangan, bukan tambahan.

## Tim dan peran
- Yughi: Ketua, Backend
- Bagja: Frontend dan fitur suara
- Lala: UI/UX, QA, dokumentasi (pemilik repo GitHub)

## Peran pengguna
Kolom `users.role` berisi `buyer`, `seller`, atau `admin` (enum MySQL, teks di SQLite).
- buyer: pembeli, sesi dan halaman di /keranjang, /checkout, /pesanan, /favorit
- seller: penjual, sesi dan halaman di /penjual/* dan /seller/*
- admin: pengelola, sesi dan halaman di /admin/*

Akun punya kolom `users.is_active` (boolean, default true). Akun nonaktif tidak bisa
masuk ("Akun Anda dinonaktifkan. Silakan hubungi admin.") dan langsung dikeluarkan
dari sesi pada permintaan berikutnya (middleware `EnsureUserIsActive`).
Peran admin tidak bisa dipilih saat pendaftaran publik; akun admin dibuat dengan
`php artisan admin:create --name=... --email=... --password=...`.

## Stack
- Laravel (PHP 8.2+), Blade, MySQL
- Tailwind CSS + Vite (resources/css/app.css, resources/js/app.js); jalankan
  `npm install` sekali, lalu `npm run dev` saat kerja atau `npm run build` untuk demo
- JavaScript murni (tanpa framework, tanpa Livewire); modul suara di resources/js/voice/
- Web Speech API: SpeechRecognition (lang "id-ID") dan SpeechSynthesis
- Auth session buatan sendiri (JANGAN pakai Breeze/Jetstream), middleware role

## Aturan aksesibilitas (wajib)
- lang="id"; header/nav/main/footer; satu h1 per halaman; heading berurutan.
- Skip link pertama di body: "Lompat ke konten utama" menuju #konten-utama.
- Semua input punya <label> terlihat. Error validasi: ringkasan di atas form dengan
  role="alert" dan tabindex="-1", fokus dipindah ke sana.
- Tanpa CAPTCHA, tanpa modal/pop-up, tanpa perubahan layar mendadak, tanpa auto-refresh.
- Pembaruan dinamis lewat aria-live="polite" (hasil cari) atau role="status"/"alert".
- Nama tombol/tautan harus bermakna ("Dengarkan deskripsi Kue Lapis"), bukan "klik di sini".
  Tombol hanya-ikon wajib aria-label.
- Setiap fitur suara WAJIB punya alternatif ketik biasa dan tidak boleh error bila
  SpeechRecognition tidak didukung

## Rute admin (backend)
Semua di dalam middleware `auth` + `role:admin`. Peran lain mendapat 403 dengan
pesan "Akses ditolak. Halaman ini khusus admin." dan tamu diarahkan ke /login.
- GET /admin/dashboard (admin.dashboard): jumlah pembeli, penjual, admin, produk, pesanan, dan pesanan per status
- GET /admin/users (admin.users): daftar pembeli dan penjual, filter ?role= dan ?q=
- PATCH /admin/users/{user}/status (admin.users.status): aktif/nonaktifkan akun
- GET /admin/products (admin.products): semua produk beserta deskripsi audio, filter ?q=
- GET /admin/orders (admin.orders): semua pesanan, filter ?status=

Tampilan admin ada di resources/views/admin/ (dashboard, users, products, orders,
navigasi) dan ditulis oleh backend; Bagja dan Lala boleh merapikan tampilannya
tanpa mengubah kontrak rute dan data yang dipakai backend.

## Rute autentikasi dan CRUD akun (backend)
Semua memakai sesi buatan sendiri dan middleware `auth` / `guest`.
- GET/POST /masuk (login.masuk, login.masuk.store) dan alias /login (login, login.store)
- GET/POST /daftar (register.daftar, register.daftar.store) dan alias /register
- POST /keluar (logout.keluar) dan alias /logout (logout)
- GET /akun (akun) dan alias /profil (profil): data akun + form ubah data, ubah kata
  sandi, hapus akun, dan keluar
- PATCH /akun (akun.update): ubah nama dan email
- PATCH /akun/sandi (akun.sandi): ganti kata sandi (butuh kata sandi lama)
- DELETE /akun (akun.hapus): hapus akun sendiri (butuh kata sandi), data produk dan
  pesanan terkait ikut terhapus

Tampilan akun ada di resources/views/akun/index.blade.php (ditulis backend).
Skema MySQL untuk impor ke phpMyAdmin tersedia di database/suaranniaga.sql
(struktur saja, tanpa data akun; isi data demo dengan `php artisan db:seed`).