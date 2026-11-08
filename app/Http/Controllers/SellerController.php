<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Harga;
use App\Support\ModeAksesibilitas;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerController extends Controller
{
    /**
     * Langkah pengisian produk pada Mode Aksesibilitas.
     */
    public const LANGKAH_PRODUK = ['nama', 'harga', 'stok', 'deskripsi', 'simpan'];

    /**
     * Kunci session tempat isian produk dikumpulkan antar langkah.
     */
    private const KUNCI_PRODUK = 'produk_langkah';

    /**
     * Isian tiap langkah produk Mode Aksesibilitas.
     *
     * @return array<string, array<string, string>>
     */
    public static function langkahProduk(): array
    {
        return [
            'nama' => [
                'judul' => 'Sebutkan nama produk',
                'instruksi' => 'Ucapkan nama produk yang ingin Anda jual, misalnya beras setra ramos lima kilogram.',
                'label' => 'Nama produk',
                'placeholder' => 'Ketik nama produk',
                'tipe' => 'text',
                'berikutnya' => 'informasi harga',
            ],
            'harga' => [
                'judul' => 'Sebutkan harga produk',
                'instruksi' => 'Ucapkan harga dalam rupiah, misalnya tujuh puluh dua ribu.',
                'label' => 'Harga produk',
                'placeholder' => 'Ketik harga, contoh 72000',
                'tipe' => 'text',
                'berikutnya' => 'informasi stok',
            ],
            'stok' => [
                'judul' => 'Sebutkan jumlah stok',
                'instruksi' => 'Ucapkan jumlah barang yang tersedia, misalnya dua puluh empat.',
                'label' => 'Jumlah stok',
                'placeholder' => 'Ketik jumlah stok',
                'tipe' => 'text',
                'berikutnya' => 'informasi deskripsi',
            ],
            'deskripsi' => [
                'judul' => 'Jelaskan deskripsi produk',
                'instruksi' => 'Ucapkan deskripsi singkat barang, ukuran, dan kelebihannya.',
                'label' => 'Deskripsi produk',
                'placeholder' => 'Ketik deskripsi produk',
                'tipe' => 'text',
                'berikutnya' => 'penyimpanan',
            ],
            'simpan' => [
                'judul' => 'Periksa dan simpan produk',
                'instruksi' => 'Dengarkan detail produk, lalu simpan bila datanya sudah benar.',
                'label' => 'Simpan produk',
                'placeholder' => null,
                'tipe' => 'text',
                'berikutnya' => 'penyimpanan',
            ],
        ];
    }

    /**
     * Ringkasan toko memakai angka nyata dari tabel products dan orders.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $produk = $user->products()->orderByDesc('id')->get();
        $pesanan = $this->pesananMilik($user);

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.penjual.beranda', [
                'title' => 'Beranda penjual',
            ]);
        }

        $kartu = [
            [
                'judul' => 'Produk aktif',
                'deskripsi' => $produk->count().' produk terjual di toko Anda',
            ],
            [
                'judul' => 'Pesanan baru',
                'deskripsi' => $pesanan->where('status', Order::STATUS_BARU)->count().' pesanan menunggu diproses',
            ],
            [
                'judul' => 'Total penjualan',
                'deskripsi' => Rupiah::format($pesanan->sum('total_price')),
            ],
        ];

        return $this->halaman('penjual.dashboard', [
            'title' => 'Dashboard Penjual',
            'mobileTitle' => 'Ringkasan toko',
            'context' => 'Dashboard penjual • Ringkasan aktivitas toko',
            'active' => 'ringkasan',
            'kartu' => $kartu,
            'produk' => $produk,
            'pesanan' => $pesanan->take(5),
        ]);
    }

    public function produk(Request $request): View
    {
        $daftar = $request->user()->products()->with('seller')->orderByDesc('id')->get();

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.penjual.produk', [
                'title' => 'Produk saya',
                'produk' => $daftar,
                'halaman' => max(1, (int) $request->query('halaman', 1)),
            ]);
        }

        return $this->halaman('penjual.produk', [
            'title' => 'Produk Saya',
            'mobileTitle' => 'Produk saya',
            'context' => 'Produk saya • Daftar produk aktif',
            'active' => 'produk',
            'produk' => $daftar,
            'products' => $daftar,
        ]);
    }

    public function createProduct(Request $request): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return $this->halaman('penjual.tambah-produk', [
                'title' => 'Tambah Produk',
                'mobileTitle' => 'Tambah produk',
                'context' => 'Tambah produk • Alur input suara bertahap',
                'active' => 'tambah',
            ]);
        }

        $request->session()->forget(self::KUNCI_PRODUK);

        return redirect()->route('penjual.produk.tambah.langkah', 'nama');
    }

    /**
     * Menampilkan satu langkah pengisian produk Mode Aksesibilitas.
     */
    public function showLangkahProduk(Request $request, string $langkah): View|RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.produk.tambah');
        }

        if (! in_array($langkah, self::LANGKAH_PRODUK, true)) {
            abort(404);
        }

        return view('aksesibilitas.penjual.tambah-produk', [
            'title' => 'Tambah produk dengan suara',
            'langkah' => $langkah,
            'konfigurasi' => self::langkahProduk(),
            'terisi' => $request->session()->get(self::KUNCI_PRODUK, []),
        ]);
    }

    /**
     * Menyimpan isian satu langkah produk dan lanjut ke langkah berikutnya.
     */
    public function storeLangkahProduk(Request $request, string $langkah): RedirectResponse
    {
        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.produk.tambah');
        }

        if (! in_array($langkah, self::LANGKAH_PRODUK, true)) {
            abort(404);
        }

        $terisi = $request->session()->get(self::KUNCI_PRODUK, []);

        $data = match ($langkah) {
            'nama' => $request->validate([
                'name' => ['required', 'string', 'max:120'],
            ], [
                'name.required' => 'Nama produk wajib diisi.',
                'name.max' => 'Nama produk maksimal 120 karakter.',
            ]),
            'harga' => $request->validate([
                'price' => ['required', 'string', 'max:30'],
            ], [
                'price.required' => 'Harga produk wajib diisi.',
                'price.max' => 'Harga produk terlalu panjang.',
            ]),
            'stok' => $request->validate([
                'stock' => ['nullable', 'string', 'max:30'],
            ], [
                'stock.max' => 'Jumlah stok terlalu panjang.',
            ]),
            'deskripsi' => $request->validate([
                'description' => ['required', 'string', 'min:10', 'max:1000'],
            ], [
                'description.required' => 'Deskripsi produk wajib diisi.',
                'description.min' => 'Deskripsi produk minimal 10 karakter.',
                'description.max' => 'Deskripsi produk maksimal 1000 karakter.',
            ]),
            default => [],
        };

        $data = array_merge($terisi, $data);

        if ($langkah === 'harga') {
            $data['price'] = Harga::rupiah($data['price']);
        }

        if ($langkah === 'stok') {
            $data['stock'] = Harga::bilangan($data['stock'] ?? 0);
        }

        $request->session()->put(self::KUNCI_PRODUK, $data);

        $berikutnya = self::LANGKAH_PRODUK[array_search($langkah, self::LANGKAH_PRODUK, true) + 1];

        return redirect()->route('penjual.produk.tambah.langkah', $berikutnya);
    }

    /**
     * Memulai pengisian ulang produk yang sudah ada.
     */
    public function editProduk(Request $request, Product $produk): RedirectResponse
    {
        abort_unless($produk->seller_id === $request->user()->id, 403, 'Produk ini bukan milik Anda.');

        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.produk');
        }

        $request->session()->put(self::KUNCI_PRODUK, [
            'produk_id' => $produk->id,
            'name' => $produk->name,
            'price' => $produk->price,
            'stock' => $produk->stock,
            'description' => $produk->description,
        ]);

        return redirect()->route('penjual.produk.tambah.langkah', 'nama');
    }

    /**
     * Halaman konfirmasi hapus produk Mode Aksesibilitas.
     */
    public function konfirmasiHapusProduk(Request $request, Product $produk): View|RedirectResponse
    {
        abort_unless($produk->seller_id === $request->user()->id, 403, 'Produk ini bukan milik Anda.');

        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.produk');
        }

        return view('aksesibilitas.penjual.hapus-produk', [
            'title' => 'Hapus produk',
            'produk' => $produk,
        ]);
    }

    public function destroyProduk(Request $request, Product $produk): RedirectResponse
    {
        abort_unless($produk->seller_id === $request->user()->id, 403, 'Produk ini bukan milik Anda.');

        $nama = $produk->name;

        $produk->delete();

        return redirect()->route('penjual.produk')
            ->with('status', $nama.' dihapus dari katalog.');
    }

    public function pesanan(Request $request): View
    {
        $daftar = $this->pesananMilik($request->user());

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.penjual.pesanan', [
                'title' => 'Pesanan masuk',
                'pesanan' => $daftar,
                'halaman' => max(1, (int) $request->query('halaman', 1)),
            ]);
        }

        return $this->halaman('penjual.pesanan', [
            'title' => 'Pesanan Masuk',
            'mobileTitle' => 'Pesanan masuk',
            'context' => 'Pesanan masuk • Antrean pesanan toko',
            'active' => 'pesanan-masuk',
            'pesanan' => $daftar,
            'orders' => $daftar,
        ]);
    }

    /**
     * Halaman status pesanan Mode Aksesibilitas.
     */
    public function status(Request $request): View|RedirectResponse
    {
        $daftar = $this->pesananMilik($request->user());

        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.pesanan');
        }

        return view('aksesibilitas.penjual.status', [
            'title' => 'Status pesanan',
            'pesanan' => $daftar,
            'halaman' => max(1, (int) $request->query('halaman', 1)),
        ]);
    }

    /**
     * Halaman konfirmasi penolakan pesanan Mode Aksesibilitas.
     */
    public function konfirmasiTolakPesanan(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless(
            $order->product()->where('seller_id', $request->user()->id)->exists(),
            403,
            'Pesanan ini bukan untuk produk Anda.'
        );

        if (! ModeAksesibilitas::aktif($request)) {
            return redirect()->route('penjual.pesanan');
        }

        return view('aksesibilitas.penjual.tolak-pesanan', [
            'title' => 'Tolak pesanan',
            'pesanan' => $order,
        ]);
    }

    /**
     * Menolak pesanan: pesanan dihapus dan barang dikembalikan ke stok.
     */
    public function tolakPesanan(Request $request, Order $order): RedirectResponse
    {
        abort_unless(
            $order->product()->where('seller_id', $request->user()->id)->exists(),
            403,
            'Pesanan ini bukan untuk produk Anda.'
        );

        $order->product?->increment('stock', $order->quantity);

        $order->delete();

        return redirect()->route('penjual.pesanan')
            ->with('status', 'Pesanan ditolak dan barang dikembalikan ke stok.');
    }

    public function toko(Request $request): View|RedirectResponse
    {
        if (ModeAksesibilitas::aktif($request)) {
            return redirect()->route('profil');
        }

        return $this->halaman('penjual.toko', [
            'title' => 'Toko Saya',
            'mobileTitle' => 'Toko saya',
            'context' => 'Toko saya • Profil toko dan pengiriman',
            'active' => 'toko',
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        // Harga dan stok bisa datang dari dikte suara, misalnya "72 ribu".
        $request->merge([
            'price' => Harga::rupiah($request->input('price')),
            'stock' => Harga::bilangan($request->input('stock')),
        ]);

        // Deskripsi audio boleh kosong pada langkah suara, lalu diisi dari
        // nama dan deskripsi produk supaya tetap bisa dibacakan.
        $request->merge([
            'audio_description' => trim((string) $request->input('audio_description')) !== ''
                ? $request->input('audio_description')
                : trim((string) $request->input('name').'. '.(string) $request->input('description')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'audio_description' => ['required', 'string', 'min:10'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 120 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka rupiah.',
            'price.min' => 'Harga harus lebih besar dari nol.',
            'stock.integer' => 'Stok harus berupa angka.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'audio_description.required' => 'Deskripsi audio wajib diisi.',
            'audio_description.min' => 'Deskripsi audio minimal 10 karakter.',
            'image_url.string' => 'Tautan gambar harus berupa teks.',
        ]);

        $produk = $this->simpanProduk($request, $data);

        if (ModeAksesibilitas::aktif($request)) {
            $request->session()->forget(self::KUNCI_PRODUK);

            return redirect()->route('penjual.produk')
                ->with('status', $produk->name.' berhasil disimpan.');
        }

        return redirect()->route('seller.dashboard')
            ->with('status', 'Produk berhasil disimpan');
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ], [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status pesanan tidak dikenal.',
        ]);

        abort_unless(
            $order->product()->where('seller_id', $request->user()->id)->exists(),
            403,
            'Pesanan ini bukan untuk produk Anda.'
        );

        if (! $order->bisaMajuKe($data['status'])) {
            return back()->withErrors([
                'status' => 'Status pesanan tidak bisa dikembalikan ke langkah sebelumnya.',
            ]);
        }

        $order->update(['status' => $data['status']]);

        return back()->with('status', 'Status pesanan diperbarui menjadi '.($order->status_label).'.');
    }

    /**
     * Menyimpan produk baru, atau memperbarui produk yang sedang diedit.
     *
     * @param  array<string, mixed>  $data
     */
    private function simpanProduk(Request $request, array $data): Product
    {
        $attributes = [
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
            'description' => $data['description'] ?? $data['audio_description'],
            'audio_description' => $data['audio_description'],
            'image_url' => $data['image_url'] ?? null,
        ];

        $produkLama = null;

        if ($request->filled('produk_id')) {
            $produkLama = $request->user()->products()->find($request->input('produk_id'));

            abort_if($produkLama === null, 403, 'Produk ini bukan milik Anda.');
        }

        if ($produkLama !== null) {
            $produkLama->update($attributes);

            return $produkLama;
        }

        return $request->user()->products()->create($attributes);
    }

    /**
     * Pesanan untuk produk milik penjual ini, dari yang terbaru.
     *
     * @return \Illuminate\Support\Collection<int, Order>
     */
    private function pesananMilik(User $user)
    {
        return Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $user->id))
            ->with(['product', 'buyer'])
            ->orderByDesc('id')
            ->get();
    }
}
