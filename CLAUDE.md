# SuaraNiaga: konteks proyek

Platform e-commerce inklusif dua arah berbasis suara untuk penyandang tunanetra.
Hackathon CR YOUTH 2026, 12 jam, tim 3 orang. Pengguna utama memakai screen reader
(NVDA, TalkBack, VoiceOver). Aksesibilitas adalah dasar rancangan, bukan tambahan.

## Stack
- Laravel (PHP 8.2+), Blade, MySQL
- CSS biasa di public/css/suaraniaga.css (TANPA Vite/Tailwind/npm build)
- JavaScript murni di public/js/ (tanpa framework, tanpa Livewire)
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
  SpeechRecognition tidak didukung (sembunyikan tombol mic, tampilkan pesan status).
- Fokus selalu terlihat dan urutan Tab logis.

## Desain (tanpa Figma, ikuti ini)
Warna (CSS variables di :root):
  --bg #FFFFFF | --text #111111 | --primary #1F3A5F (navy: header, tombol mic)
  --action #1E6B3C (hijau: Dengar, Simpan, Pesan) | --record #F0E6F8 (area rekam)
  --card #EAF0FB | --border #1F3A5F | --focus #FFB300 | --danger #B00020
Aturan: teks dasar 18px (judul h1 28px), tinggi tombol minimal 48px, border 2px,
sudut membulat 8px, fokus outline 3px solid var(--focus) offset 2px, jarak antar
elemen lega, satu kolom di ponsel. Rasio kontras minimal 4.5:1. Jangan pakai warna
sebagai satu-satunya penanda. Hormati prefers-reduced-motion. Tidak ada animasi.
Font: system-ui.

Layout halaman:
- Beranda pembeli: header (nama + nav) > form cari (input "Cari produk..." + tombol
  "MIC") > region hasil (aria-live) berisi kartu produk (nama, harga "Rp10.000",
  tombol hijau "Dengar", tautan "Lihat detail").
- Detail produk: nama (h1), harga, deskripsi, deskripsi audio (teks), tombol "Dengarkan
  deskripsi", tombol "Pesan sekarang".
- Tambah produk (penjual): area besar ungu muda dengan tombol "REKAM SUARA PRODUK",
  lalu field Nama produk, Harga (Rp), Deskripsi, Deskripsi audio (boleh dikte/ketik),
  tombol hijau "SIMPAN PRODUK".
- Dasbor penjual: tombol "Aktifkan suara notifikasi", daftar pesanan (status +
  tombol ubah status), daftar produk milik penjual.

## Database (3 tabel inti)
users: id, name, email (unique), password, role ('buyer'|'seller')
products: id, seller_id FK users, name, price (unsigned int, rupiah), description,
  audio_description (WAJIB, text), image_url (nullable)
orders: id, buyer_id FK users, product_id FK products, status
  ('baru'|'diproses'|'dikirim'|'selesai', default 'baru'), total_price, timestamps

## Kontrak route dan JSON (jangan diubah tanpa kabar di grup)
Web (session auth):
  GET  /                      beranda pembeli (katalog, ?q= untuk cari)
  GET  /products/{id}         detail produk
  POST /products/{id}/order   pesan (buyer) lalu redirect ke /orders/{id}/konfirmasi
  GET  /orders/{id}/konfirmasi konfirmasi (pembayaran disimulasikan)
  GET/POST /register, /login ; POST /logout
  GET  /seller/dashboard
  GET  /seller/products/create ; POST /seller/products
  PATCH /seller/orders/{id}/status   (body: status)
API JSON (session auth, middleware auth):
  GET /api/products?q=kue
    -> {"count":2,"products":[{"id":1,"name":"Kue Lapis","price":10000,
       "price_text":"Rp10.000","audio_description":"...","url":"/products/1"}]}
  GET /api/seller/orders/new?after_id=0   (seller)
    -> {"latest_id":5,"orders":[{"id":5,"product_name":"Kue Lapis",
       "buyer_name":"Sari","total_price":10000,"total_text":"Rp10.000","status":"baru"}]}

## Kepemilikan file (JANGAN edit file milik orang lain tanpa kabar di grup)
- Yughi: app/, database/migrations, routes/, config/, .env.example
- Lala: resources/views/layouts/, resources/views/buyer/, resources/views/seller/,
  public/js/
- Bagja: public/css/suaraniaga.css, resources/views/auth/, database/seeders/,
  README.md, docs/, tests/manual/
Yughi membuat view placeholder (hanya h1) di commit awal agar route tidak error;
setelah itu view hanya diedit pemiliknya.

## Git
Branch: main + branch fitur pendek (feature/auth-roles, feature/products-orders,
feature/voice-features, feature/ui-a11y). Sebelum kerja dan sebelum push:
git pull --rebase origin main. Commit kecil dan sering, format "feat: ...", "fix: ...",
"docs: ...". Merge ke main hanya kalau php artisan serve jalan tanpa error.
Jangan commit .env. Feature freeze jam ke-10.

## Di luar lingkup MVP
Pintasan keyboard khusus, QRIS/pembayaran nyata, logistik, AI deskripsi foto.