<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Checkout pembeli.
 *
 * Checkout membaca isi keranjang di sesi, membuat satu pesanan untuk setiap
 * barang, lalu mengosongkan keranjang.
 */
class CheckoutController extends Controller
{
    public function index(Request $request, KeranjangController $keranjang): View
    {
        $barang = $keranjang->isi($request);

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.checkout', [
                'title' => 'Checkout',
                'barang' => $barang,
                'total' => $barang->sum(fn (array $row) => $row['subtotal']),
            ]);
        }

        return $this->halaman('checkout', [
            'title' => 'Checkout',
            'context' => 'Checkout • Formulir sederhana dan terstruktur',
            'active' => 'keranjang',
            'barang' => $barang,
            'total' => $barang->sum(fn (array $row) => $row['subtotal']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['required', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'metode' => ['required', Rule::in(array_keys(Order::METODE_PEMBAYARAN))],
        ], [
            'nama.required' => 'Nama penerima wajib diisi.',
            'nama.max' => 'Nama penerima maksimal 120 karakter.',
            'telepon.max' => 'Nomor telepon maksimal 30 karakter.',
            'alamat.required' => 'Alamat pengiriman wajib diisi.',
            'alamat.max' => 'Alamat pengiriman maksimal 500 karakter.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'metode.in' => 'Metode pembayaran tidak dikenal.',
        ]);

        $keranjang = $request->session()->get(KeranjangController::KUNCI_SESI, []);

        if ($keranjang === []) {
            return redirect()->route('keranjang')
                ->withErrors(['keranjang' => 'Keranjang masih kosong.']);
        }

        $produk = Product::query()
            ->whereIn('id', array_keys($keranjang))
            ->get()
            ->keyBy('id');

        $ids = DB::transaction(function () use ($keranjang, $produk, $request, $data) {
            $ids = [];

            foreach ($keranjang as $id => $jumlah) {
                $item = $produk->get($id);

                if ($item === null) {
                    continue;
                }

                $banyak = min((int) $jumlah, $item->stock);

                if ($banyak < 1) {
                    continue;
                }

                $item->decrement('stock', $banyak);

                $order = new Order;
                $order->buyer_id = $request->user()->id;
                $order->product_id = $item->id;
                $order->quantity = $banyak;
                $order->status = Order::STATUS_BARU;
                $order->total_price = $item->price * $banyak;
                $order->recipient_name = $data['nama'];
                $order->recipient_phone = $data['telepon'] ?? null;
                $order->shipping_address = $data['alamat'];
                $order->note = $data['catatan'] ?? null;
                $order->payment_method = $data['metode'];
                $order->save();

                $ids[] = $order->id;
            }

            return $ids;
        });

        if ($ids === []) {
            return redirect()->route('keranjang')
                ->withErrors(['keranjang' => 'Barang di keranjang sudah tidak tersedia.']);
        }

        $request->session()->forget(KeranjangController::KUNCI_SESI);
        $request->session()->put('pesanan_baru', $ids);

        return redirect()->route('pesanan.sukses');
    }
}
