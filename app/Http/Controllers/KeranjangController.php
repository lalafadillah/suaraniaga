<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Keranjang pembeli.
 *
 * Isi keranjang disimpan di sesi sebagai peta id produk ke jumlah, lalu
 * dirangkai dengan data produk terbaru dari database supaya harga dan stok
 * yang ditampilkan selalu benar.
 */
class KeranjangController extends Controller
{
    public const KUNCI_SESI = 'keranjang';

    public function index(Request $request): View
    {
        $barang = $this->isi($request);

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.keranjang', [
                'title' => 'Keranjang saya',
                'barang' => $barang,
                'total' => $barang->sum(fn (array $row) => $row['subtotal']),
                'halaman' => max(1, (int) $request->query('halaman', 1)),
            ]);
        }

        return $this->halaman('keranjang', [
            'title' => 'Keranjang',
            'context' => 'Keranjang • Ringkasan barang sebelum checkout',
            'active' => 'keranjang',
            'barang' => $barang,
            'total' => $barang->sum(fn (array $row) => $row['subtotal']),
        ]);
    }

    public function store(Request $request, Product $produk): RedirectResponse
    {
        if (! $produk->tersedia()) {
            return back()->withErrors([
                'keranjang' => 'Stok '.$produk->name.' sudah habis.',
            ]);
        }

        $keranjang = $request->session()->get(self::KUNCI_SESI, []);
        $jumlah = (int) ($keranjang[$produk->id] ?? 0) + 1;

        $keranjang[$produk->id] = min($jumlah, $produk->stock);
        $request->session()->put(self::KUNCI_SESI, $keranjang);

        return redirect()->route('keranjang')
            ->with('status', $produk->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $produk): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'jumlah.required' => 'Jumlah barang wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka.',
            'jumlah.min' => 'Jumlah minimal satu barang.',
            'jumlah.max' => 'Jumlah maksimal 20 barang.',
        ]);

        $jumlah = $data['jumlah'];

        if ($jumlah > $produk->stock) {
            return back()->withErrors([
                'jumlah' => 'Stok '.$produk->name.' hanya tersisa '.$produk->stock.' barang.',
            ]);
        }

        $keranjang = $request->session()->get(self::KUNCI_SESI, []);
        $keranjang[$produk->id] = $jumlah;
        $request->session()->put(self::KUNCI_SESI, $keranjang);

        return redirect()->route('keranjang')
            ->with('status', 'Jumlah '.$produk->name.' diperbarui menjadi '.$jumlah.'.');
    }

    public function destroy(Request $request, Product $produk): RedirectResponse
    {
        $keranjang = $request->session()->get(self::KUNCI_SESI, []);
        unset($keranjang[$produk->id]);
        $request->session()->put(self::KUNCI_SESI, $keranjang);

        return redirect()->route('keranjang')
            ->with('status', $produk->name.' dihapus dari keranjang.');
    }

    /**
     * Isi keranjang dari sesi, dirangkai dengan produk yang masih ada.
     *
     * Produk yang sudah habis stok dikeluarkan dari keranjang dan
     * pemberitahuan diteruskan ke halaman.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function isi(Request $request): Collection
    {
        $keranjang = $request->session()->get(self::KUNCI_SESI, []);

        if ($keranjang === []) {
            return collect();
        }

        $produk = Product::query()
            ->whereIn('id', array_keys($keranjang))
            ->get()
            ->keyBy('id');

        $barang = collect();
        $sisa = [];
        $habis = [];

        foreach ($keranjang as $id => $jumlah) {
            $item = $produk->get($id);

            if ($item === null) {
                continue;
            }

            if (! $item->tersedia()) {
                $habis[] = $item->name;

                continue;
            }

            $banyak = max(1, min((int) $jumlah, $item->stock));

            $barang->push([
                'produk' => $item,
                'nama' => $item->name,
                'harga' => $item->price,
                'stok' => $item->stock,
                'jumlah' => $banyak,
                'subtotal' => $item->price * $banyak,
            ]);

            $sisa[$id] = $banyak;
        }

        $request->session()->put(self::KUNCI_SESI, $sisa);

        if ($habis !== []) {
            $request->session()->flash(
                'status',
                implode(', ', $habis).' sudah habis stok dan dikeluarkan dari keranjang.'
            );
        }

        return $barang;
    }
}
