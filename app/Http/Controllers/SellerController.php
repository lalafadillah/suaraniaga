<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $seller = $request->user();

        $orders = Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $seller->id))
            ->with(['product', 'buyer'])
            ->latest()
            ->get();

        $products = $seller->products()->latest()->get();

        return view('seller.dashboard', compact('orders', 'products'));
    }

    public function createProduct(): View
    {
        return view('seller.products.create');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'audio_description' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka rupiah.',
            'price.min' => 'Harga tidak boleh negatif.',
            'description.required' => 'Deskripsi wajib diisi.',
            'audio_description.required' => 'Deskripsi audio wajib diisi.',
        ]);

        $request->user()->products()->create($data);

        return redirect()->route('seller.dashboard')
            ->with('status', 'Produk berhasil disimpan.');
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

        $order->update(['status' => $data['status']]);

        return back()->with('status', 'Status pesanan diperbarui menjadi '.$data['status'].'.');
    }
}
