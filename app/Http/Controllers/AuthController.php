<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Peran yang boleh dipilih saat pendaftaran publik.
     * Peran admin hanya bisa dibuat lewat perintah `php artisan admin:create`.
     */
    private const PERAN_DITERIMA = [User::ROLE_BUYER, User::ROLE_SELLER];

    /**
     * Alias bahasa Indonesia untuk peran, dipakai form pendaftaran lama.
     */
    private const ALIAS_PERAN = [
        'pembeli' => User::ROLE_BUYER,
        'penjual' => User::ROLE_SELLER,
    ];

    /**
     * Langkah pendaftaran Mode Aksesibilitas. Tiap langkah hanya satu kolom.
     */
    public const LANGKAH_DAFTAR = ['nama', 'email', 'telepon', 'alamat', 'sandi', 'konfirmasi', 'peran'];

    /**
     * Kunci session tempat isian pendaftaran dikumpulkan antar langkah.
     */
    private const KUNCI_DAFTAR = 'daftar_langkah';

    /**
     * Kunci session tempat alamat email menunggu kata sandi.
     */
    private const KUNCI_EMAIL_MASUK = 'masuk_email';

    /**
     * Isian tiap langkah pendaftaran Mode Aksesibilitas.
     *
     * @return array<string, array<string, string>>
     */
    public static function langkahDaftar(): array
    {
        $instruksi = 'Satu informasi per halaman agar mudah dibaca dan diisi.';

        return [
            'nama' => [
                'judul' => 'Siapa nama lengkap Anda?',
                'instruksi' => $instruksi,
                'label' => 'Nama lengkap',
                'placeholder' => 'Ketik nama lengkap Anda',
                'tipe' => 'text',
                'bantuan' => 'Nama seperti pada dokumen resmi Anda.',
            ],
            'email' => [
                'judul' => 'Alamat email Anda?',
                'instruksi' => $instruksi,
                'label' => 'Alamat email',
                'placeholder' => 'Ketik alamat email Anda',
                'tipe' => 'email',
                'bantuan' => 'Email dipakai untuk masuk dan menerima kabar pesanan.',
            ],
            'telepon' => [
                'judul' => 'Nomor telepon Anda?',
                'instruksi' => $instruksi,
                'label' => 'Nomor telepon',
                'placeholder' => 'Ketik nomor telepon Anda',
                'tipe' => 'tel',
                'bantuan' => 'Penjual menghubungi nomor ini saat pesanan siap.',
            ],
            'alamat' => [
                'judul' => 'Alamat lengkap Anda?',
                'instruksi' => $instruksi,
                'label' => 'Alamat lengkap',
                'placeholder' => 'Ketik alamat lengkap Anda',
                'tipe' => 'text',
                'bantuan' => 'Alamat ini dipakai lagi saat kamu memesan barang.',
            ],
            'sandi' => [
                'judul' => 'Buat kata sandi Anda',
                'instruksi' => $instruksi,
                'label' => 'Kata sandi baru',
                'placeholder' => 'Ketik kata sandi baru Anda',
                'tipe' => 'password',
                'bantuan' => 'Kata sandi minimal 8 karakter.',
            ],
            'konfirmasi' => [
                'judul' => 'Ulangi kata sandi Anda',
                'instruksi' => $instruksi,
                'label' => 'Ulangi kata sandi',
                'placeholder' => 'Ketik kata sandi yang sama lagi',
                'tipe' => 'password',
                'bantuan' => 'Kedua tulisan harus sama.',
            ],
            'peran' => [
                'judul' => 'Anda ingin menggunakan SuaraNiaga sebagai apa?',
                'instruksi' => 'Pilih salah satu. Pilihan peran ditampilkan sebagai tombol besar.',
                'label' => 'Peran akun',
                'placeholder' => null,
                'tipe' => 'pilihan',
                'bantuan' => 'Peran bisa diubah nanti dari halaman profil.',
            ],
        ];
    }

    public function showRegister(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.daftar', [
                'title' => 'Buat akun baru',
                'langkah' => 'nama',
                'daftar' => self::langkahDaftar(),
                'terisi' => $request->session()->get(self::KUNCI_DAFTAR, []),
            ]);
        }

        return view('auth.daftar', [
            'title' => 'Daftar Akun',
        ]);
    }

    /**
     * Menampilkan satu langkah pendaftaran Mode Aksesibilitas.
     */
    public function showDaftarLangkah(Request $request, string $langkah): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('register.daftar');
        }

        if (! in_array($langkah, self::LANGKAH_DAFTAR, true)) {
            abort(404);
        }

        return view('aksesibilitas.daftar', [
            'title' => 'Buat akun baru',
            'langkah' => $langkah,
            'daftar' => self::langkahDaftar(),
            'terisi' => $request->session()->get(self::KUNCI_DAFTAR, []),
        ]);
    }

    /**
     * Menyimpan isian satu langkah dan lanjut ke langkah berikutnya.
     */
    public function storeDaftarLangkah(Request $request, string $langkah): RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('register.daftar.store');
        }

        if (! in_array($langkah, self::LANGKAH_DAFTAR, true)) {
            abort(404);
        }

        $terisi = $request->session()->get(self::KUNCI_DAFTAR, []);

        $data = match ($langkah) {
            'nama' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ], [
                'name.required' => 'Nama wajib diisi.',
                'name.max' => 'Nama maksimal 255 karakter.',
            ]),
            'email' => $request->validate([
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            ]),
            'telepon' => $request->validate([
                'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            ], [
                'phone.required' => 'Nomor telepon wajib diisi.',
                'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda hubung.',
                'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            ]),
            'alamat' => $request->validate([
                'address' => ['required', 'string', 'max:1000'],
            ], [
                'address.required' => 'Alamat wajib diisi.',
                'address.max' => 'Alamat maksimal 1000 karakter.',
            ]),
            'sandi' => $request->validate([
                'password' => ['required', 'string', 'min:8', 'max:255'],
            ], [
                'password.required' => 'Kata sandi wajib diisi.',
                'password.min' => 'Kata sandi minimal 8 karakter.',
            ]),
            'konfirmasi' => $this->validasiKonfirmasiSandi($request, $terisi),
            'peran' => $request->validate([
                'role' => ['required', Rule::in(self::PERAN_DITERIMA)],
            ], [
                'role.required' => 'Pilih salah satu peran.',
                'role.in' => 'Peran yang dipilih tidak dikenal.',
            ]),
        };

        $terisi = array_merge($terisi, $data);

        $request->session()->put(self::KUNCI_DAFTAR, $terisi);

        if ($langkah === 'peran') {
            return $this->buatAkun($request, $terisi);
        }

        $berikutnya = self::LANGKAH_DAFTAR[array_search($langkah, self::LANGKAH_DAFTAR, true) + 1];

        return redirect()->route('daftar.langkah', $berikutnya);
    }

    /**
     * Membuat akun dari isian yang sudah dikumpulkan di langkah sebelumnya.
     *
     * @param  array<string, mixed>  $terisi
     */
    public function storeDaftarSelesai(Request $request): RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('register.daftar.store');
        }

        $terisi = $request->session()->get(self::KUNCI_DAFTAR, []);

        $data = $request->validate([
            'role' => ['required', Rule::in(self::PERAN_DITERIMA)],
        ], [
            'role.required' => 'Pilih salah satu peran.',
            'role.in' => 'Peran yang dipilih tidak dikenal.',
        ]);

        $terisi['role'] = $data['role'];

        return $this->buatAkun($request, $terisi);
    }

    /**
     * Membuat akun, masuk ke sesi, lalu mengarahkan ke halaman berhasil.
     *
     * @param  array<string, mixed>  $terisi
     */
    private function buatAkun(Request $request, array $terisi): RedirectResponse
    {
        foreach (['name', 'email', 'password', 'role'] as $wajib) {
            if (! isset($terisi[$wajib])) {
                return redirect()->route('daftar.langkah', 'nama');
            }
        }

        $user = User::create([
            'name' => $terisi['name'],
            'email' => $terisi['email'],
            'phone' => $terisi['phone'] ?? null,
            'address' => $terisi['address'] ?? null,
            'password' => Hash::make($terisi['password']),
            'role' => $terisi['role'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(self::KUNCI_DAFTAR);

        return redirect()->route('daftar.berhasil');
    }

    /**
     * Konfirmasi pendaftaran berhasil Mode Aksesibilitas.
     */
    public function showDaftarBerhasil(Request $request): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('beranda');
        }

        return view('aksesibilitas.daftar-berhasil', [
            'title' => 'Akun berhasil dibuat',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if ($request->filled('role')) {
            $request->merge([
                'role' => self::ALIAS_PERAN[$request->input('role')] ?? $request->input('role'),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in(self::PERAN_DITERIMA)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.in' => 'Peran yang dipilih tidak dikenal.',
        ]);

        $role = $this->normalisasiPeran($data['role'] ?? User::ROLE_BUYER);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->tujuanPeran($role))
            ->with('status', 'Berhasil mendaftar. Selamat datang di SuaraNiaga.');
    }

    public function showLogin(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.masuk', [
                'title' => 'Masuk ke akun',
                'langkah' => 'email',
            ]);
        }

        return view('auth.masuk', [
            'title' => 'Masuk',
        ]);
    }

    /**
     * Langkah 1 Mode Aksesibilitas: menyimpan email lalu lanjut ke kata sandi.
     */
    public function storeLoginEmail(Request $request): RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('login.masuk.store');
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $request->session()->put(self::KUNCI_EMAIL_MASUK, $data['email']);

        return redirect()->route('login.masuk.sandi');
    }

    /**
     * Langkah 2 Mode Aksesibilitas: memeriksa kata sandi.
     */
    public function showLoginSandi(Request $request): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('login.masuk');
        }

        return view('aksesibilitas.masuk', [
            'title' => 'Masukkan kata sandi',
            'langkah' => 'sandi',
            'email' => $request->session()->get(self::KUNCI_EMAIL_MASUK, ''),
        ]);
    }

    public function storeLoginSandi(Request $request): RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('login.masuk.store');
        }

        $email = (string) $request->session()->get(self::KUNCI_EMAIL_MASUK, '');

        $data = $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($email === '' || ! Auth::attempt(['email' => $email, 'password' => $data['password']])) {
            return redirect()->route('login.masuk.sandi')
                ->withInput($request->only('password'))
                ->withErrors(['password' => 'Email atau kata sandi salah.']);
        }

        if (! $request->user()->isActive()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login.masuk.sandi')
                ->withErrors(['password' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        $request->session()->regenerate();
        $request->session()->forget(self::KUNCI_EMAIL_MASUK);

        return redirect()->route('login.masuk.berhasil');
    }

    /**
     * Konfirmasi masuk berhasil Mode Aksesibilitas.
     */
    public function showLoginBerhasil(Request $request): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('beranda');
        }

        return view('aksesibilitas.masuk-berhasil', [
            'title' => 'Anda berhasil masuk',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return redirect()->route($this->halamanMasuk($request))
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        if (! $request->user()->isActive()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($this->halamanMasuk($request))
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->tujuanPeran($request->user()->role)))
            ->with('status', 'Berhasil masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')
            ->with('status', 'Anda telah keluar.');
    }

    /**
     * Halaman konfirmasi keluar Mode Aksesibilitas.
     */
    public function showKeluar(Request $request): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('akun');
        }

        return view('aksesibilitas.keluar', [
            'title' => 'Keluar dari akun',
        ]);
    }

    /**
     * Memeriksa konfirmasi kata sandi pada langkah konfirmasi pendaftaran.
     *
     * @param  array<string, mixed>  $terisi
     * @return array<string, string>
     */
    private function validasiKonfirmasiSandi(Request $request, array $terisi): array
    {
        $data = $request->validate([
            'password_confirmation' => ['required', 'string'],
        ], [
            'password_confirmation.required' => 'Ulangi kata sandi wajib diisi.',
        ]);

        if (($terisi['password'] ?? null) !== $data['password_confirmation']) {
            throw ValidationException::withMessages([
                'password_confirmation' => 'Konfirmasi kata sandi tidak cocok.',
            ]);
        }

        return ['password' => $terisi['password']];
    }

    /**
     * Halaman masuk yang dipakai untuk mengembalikan galat, sesuai alias yang
     * sedang dibuka pengguna (/masuk atau /login).
     */
    private function halamanMasuk(Request $request): string
    {
        $referer = (string) $request->headers->get('referer');

        return str_contains($referer, '/masuk') ? 'login.masuk' : 'login';
    }

    private function normalisasiPeran(string $role): string
    {
        return in_array($role, self::PERAN_DITERIMA, true) ? $role : User::ROLE_BUYER;
    }

    private function tujuanPeran(string $role): string
    {
        return match ($role) {
            User::ROLE_SELLER => 'seller.dashboard',
            User::ROLE_ADMIN => 'admin.dashboard',
            default => 'beranda',
        };
    }
}
