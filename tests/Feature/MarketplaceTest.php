<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Loop marketplace: penjual Posting barang, pembeli membeli, dan pesanan itu
 * muncul kembali di sisi penjual.
 */
class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function seller(): User
    {
        return User::create([
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    private function buyer(): User
    {
        return User::create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);
    }

    public function test_penjual_post_produk_lalu_muncul_di_katalog_dan_daftar_produknya(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)->post('/penjual/produk', [
            'name' => 'Kue Lapis Uji',
            'price' => 15000,
            'stock' => 12,
            'audio_description' => 'Kue lapis multilayer dengan lapisan tipis dan manis',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'Kue Lapis Uji',
            'price' => 15000,
            'stock' => 12,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($this->buyer())->get('/cari?q=lapis')
            ->assertOk()
            ->assertSee('Kue Lapis Uji');

        $this->actingAs($seller)->get('/penjual/produk')
            ->assertOk()
            ->assertSee('Kue Lapis Uji')
            ->assertSee('Stok 12');
    }

    public function test_harga_dan_stok_dari_dikte_suara_dinormalisasi(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)->post('/penjual/produk', [
            'name' => 'Beras Ramos',
            'price' => '72.000',
            'stock' => '24',
            'audio_description' => 'Beras pulen dengan tekstur lembut dan kemasan tertutup',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('products', ['name' => 'Beras Ramos', 'price' => 72000, 'stock' => 24]);

        $this->actingAs($seller)->post('/penjual/produk', [
            'name' => 'Minyak Goreng',
            'price' => '34 ribu',
            'stock' => '10',
            'audio_description' => 'Minyak goreng kemasan dua liter untuk kebutuhan dapur',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('products', ['name' => 'Minyak Goreng', 'price' => 34000]);
    }

    public function test_pembeli_bisa_membeli_langsung_dari_halaman_detail_produk(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 5);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->get('/produk/'.$produk->id)->assertOk()->assertSee('Beli Kue Lapis');

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order', ['quantity' => 2])
            ->assertRedirect('/orders/1/konfirmasi');

        $order = Order::firstOrFail();

        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame(2, $order->quantity);
        $this->assertSame(20000, $order->total_price);
        $this->assertSame(Order::STATUS_BARU, $order->status);
        $this->assertSame(3, $produk->fresh()->stock);
    }

    public function test_pembelian_ditolak_saat_stok_habis(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 1);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order', ['quantity' => 2])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $produk->fresh()->stock);
    }

    public function test_keranjang_bisa_diisi_dan_di_checkout_membuat_pesanan(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 8);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/keranjang/'.$produk->id)->assertRedirect(route('keranjang'));

        $this->actingAs($buyer)->get(route('keranjang'))
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('Rp 10.000');

        $this->actingAs($buyer)->patch('/keranjang/'.$produk->id, ['jumlah' => 3])
            ->assertRedirect(route('keranjang'));

        $this->actingAs($buyer)->post('/checkout', [
            'nama' => 'Budi',
            'telepon' => '08123456789',
            'alamat' => 'Jl. Merdeka 10, Bandung',
            'catatan' => 'Titip ke satpam',
            'metode' => 'simulasi',
        ])->assertRedirect(route('pesanan.sukses'));

        $order = Order::firstOrFail();

        $this->assertSame(3, $order->quantity);
        $this->assertSame(30000, $order->total_price);
        $this->assertSame('Budi', $order->recipient_name);
        $this->assertSame('Jl. Merdeka 10, Bandung', $order->shipping_address);
        $this->assertSame('simulasi', $order->payment_method);
        $this->assertSame(5, $produk->fresh()->stock);

        $this->actingAs($buyer)->get(route('keranjang'))->assertOk()->assertSee('Keranjang masih kosong');
    }

    public function test_barang_bisa_dihapus_dari_keranjang(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 8);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/keranjang/'.$produk->id);
        $this->actingAs($buyer)->delete('/keranjang/'.$produk->id)->assertRedirect(route('keranjang'));

        $this->actingAs($buyer)->get(route('keranjang'))->assertOk()->assertSee('Keranjang masih kosong');
    }

    public function test_pesanan_muncul_di_daftar_pesanan_pembeli(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 5);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $this->actingAs($buyer)->get(route('pesanan'))
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('SN-000001')
            ->assertSee('Pesanan baru');
    }

    public function test_pesanan_masuk_muncul_di_penjual_dan_status_bisa_naik(): void
    {
        $seller = $this->seller();
        $produk = $this->produk($seller, 'Kue Lapis', 10000, 5);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $order = Order::firstOrFail();

        $this->actingAs($seller)->get(route('penjual.pesanan'))
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee($order->nomor)
            ->assertSee('Terima pesanan');

        $this->actingAs($seller)->get(route('penjual.dashboard'))
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('Pesanan baru');

        $this->actingAs($seller)->patch('/penjual/pesanan/'.$order->id.'/status', ['status' => 'diproses'])
            ->assertRedirect();

        $this->assertSame(Order::STATUS_DIPROSES, $order->fresh()->status);

        $this->actingAs($seller)->get(route('penjual.pesanan'))
            ->assertOk()
            ->assertSee('Sedang diproses');
    }

    public function test_penjual_lain_tidak_melihat_pesanan_pembeli(): void
    {
        $produk = $this->produk($this->seller(), 'Kue Lapis', 10000, 5);

        $this->actingAs($this->buyer())->post('/produk/'.$produk->id.'/order');

        $penjualLain = User::create([
            'name' => 'Tono',
            'email' => 'tono@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);

        $this->actingAs($penjualLain)->get(route('penjual.pesanan'))
            ->assertOk()
            ->assertDontSee('Kue Lapis');
    }

    public function test_halaman_keranjang_hanya_untuk_pembeli(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)->get(route('keranjang'))->assertForbidden();
        $this->actingAs($seller)->get(route('checkout'))->assertForbidden();
    }

    private function produk(User $seller, string $nama, int $harga, int $stok): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $nama,
            'price' => $harga,
            'stock' => $stok,
            'description' => 'Deskripsi '.$nama,
            'audio_description' => 'Deskripsi audio '.$nama,
        ]);
    }
}
