<?php

namespace App\Http\Controllers;

use App\Support\Katalog;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            return $request->user()
                ? view('aksesibilitas.beranda', ['title' => 'Beranda'])
                : view('aksesibilitas.menu', ['title' => 'Selamat datang di SuaraNiaga']);
        }

        $q = trim((string) $request->query('q', ''));

        if ($q !== '') {
            return $this->halaman('cari', [
                'title' => 'Cari Produk',
                'mobileTitle' => 'Cari produk',
                'context' => 'Pencarian produk • Input suara dan teks',
                'active' => 'cari',
                'produk' => Katalog::cari($q),
            ]);
        }

        return $this->halaman('beranda', [
            'title' => 'Beranda',
            'mobileTitle' => 'SuaraNiaga',
            'context' => 'Beranda pembeli • Fokus pada satu tindakan utama',
            'active' => 'beranda',
        ]);
    }

    public function search(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            $q = trim((string) $request->query('q', ''));

            if ($q === '') {
                return view('aksesibilitas.cari', ['title' => 'Cari produk dengan suara']);
            }

            $daftar = Katalog::cari($q);

            return view('aksesibilitas.produk', $this->halamanProduk($daftar, $request, [
                'judul' => 'Hasil pencarian suara',
                'instruksi' => 'Produk yang cocok dengan ucapan Anda. Satu produk ditampilkan dalam satu halaman.',
                'labelKecil' => 'HASIL PENCARIAN',
                'catatan' => 'Hasil ditemukan dari suara: "'.$q.'". Gunakan Beli produk ini untuk memilih produk.',
                'q' => $q,
            ]));
        }

        return $this->halaman('cari', [
            'title' => 'Cari Produk',
            'mobileTitle' => 'Cari produk',
            'context' => 'Pencarian produk • Input suara dan teks',
            'active' => 'cari',
            'produk' => Katalog::cari((string) $request->query('q', '')),
        ]);
    }

    /**
     * Daftar seluruh produk. Mode Aksesibilitas menampilkan satu produk per
     * halaman supaya pengguna tidak perlu melewati daftar panjang sekaligus.
     */
    public function daftar(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            $daftar = Katalog::semua();

            return view('aksesibilitas.produk', $this->halamanProduk($daftar, $request, [
                'judul' => 'Dengarkan daftar produk',
                'instruksi' => 'Satu produk ditampilkan pada satu halaman. Gunakan tombol Sebelumnya dan Berikutnya untuk berpindah.',
                'labelKecil' => 'KATALOG PRODUK',
                'catatan' => 'Gunakan tombol Dengarkan deskripsi untuk membaca detail produk, lalu Beli produk untuk membeli.',
                'q' => null,
            ]));
        }

        return $this->halaman('beranda', ['title' => 'Beranda', 'active' => 'beranda']);
    }

    public function show(int $produk): View
    {
        $data = Katalog::detail($produk);

        abort_if($data === null, 404, 'Produk tidak ditemukan.');

        return $this->halaman('detail-produk', [
            'title' => 'Detail Produk',
            'mobileTitle' => $data['nama'],
            'context' => 'Detail produk • Informasi utama dan deskripsi audio',
            'active' => 'cari',
            'produk' => $data,
        ]);
    }

    /**
     * Susunan data halaman produk Mode Aksesibilitas.
     *
     * @param  list<array<string, mixed>>  $daftar
     * @param  array<string, mixed>  $tambahan
     * @return array<string, mixed>
     */
    private function halamanProduk(array $daftar, Request $request, array $tambahan = []): array
    {
        $total = count($daftar);
        $halaman = max(1, min((int) $request->query('halaman', 1), max(1, $total)));

        $namaRute = $request->routeIs('cari') ? 'cari' : 'produk.daftar';
        $kunci = $request->query('q') !== null ? ['q' => (string) $request->query('q')] : [];

        $sebelumnya = $halaman > 1
            ? route($namaRute, array_merge($kunci, ['halaman' => $halaman - 1]))
            : null;

        $berikutnya = $halaman < $total
            ? route($namaRute, array_merge($kunci, ['halaman' => $halaman + 1]))
            : null;

        return array_merge($tambahan, [
            'produk' => $daftar[$halaman - 1] ?? null,
            'halaman' => $halaman,
            'total' => $total,
            'posisi' => 'PRODUK '.$halaman.' DARI '.$total,
            'sebelumnya' => $sebelumnya,
            'berikutnya' => $berikutnya,
        ]);
    }
}
