# SuaraNiaga: konteks proyek

Platform e-commerce inklusif dua arah berbasis suara untuk penyandang tunanetra.
Hackathon CR YOUTH 2026, 12 jam, tim 3 orang. Pengguna utama memakai screen reader
(NVDA, TalkBack, VoiceOver). Aksesibilitas adalah dasar rancangan, bukan tambahan.

## Tim dan peran
- Yughi: Ketua, Backend
- Bagja: Frontend dan fitur suara
- Lala: UI/UX, QA, dokumentasi (pemilik repo GitHub)

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
  SpeechRecognition tidak didukung