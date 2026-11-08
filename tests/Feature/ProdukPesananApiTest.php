<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukPesananApiTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function seller(): User
    {
        return User::create([
            'name' => 'Sari',
            'email' => 'sari+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    private function sellerLain(): User
    {
        return User::create([
            'name' => 'Tono',
            'email' => 'tono+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    private function buyer(): User
    {
        return User::create([
            'name' => 'Budi',
            'email' => 'budi+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);
    }

    private function produk(User $seller, string $nama, int $harga = 10000, string $deskripsi = 'Deskripsi produk'): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $nama,
            'price' => $harga,
            'description' => $deskripsi,
            'audio_description' => 'Deskripsi audio untuk '.$nama,
        ]);
    }

    public function test_api_produk_mengembalikan_semua_produk_terbaru(): void
    {
        $seller = $this->seller();
        $this->produk($seller, 'Beras Ramos');
        $this->produk($seller, 'Minyak Goreng');

        $response = $this->actingAs($this->buyer())->getJson('/api/products');

        $response->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('products.0.name', 'Minyak Goreng')
            ->assertJsonPath('products.0.price_text', 'Rp10.000')
            ->assertJsonPath('products.0.url', '/products/2')
            ->assertJsonStructure([
                'count',
                'products' => [['id', 'name', 'price', 'price_text', 'audio_description', 'url']],
            ]);
    }

    public function test_api_produk_mencari_pada_nama_dan_deskripsi(): void
    {
        $seller = $this->seller();
        $this->produk($seller, 'Kue Lapis', 10000, 'Kue lapis multilayer berwarna');
        $this->produk($seller, 'Kerupuk Udang', 20000, 'Kerupuk gurih');

        $this->actingAs($this->buyer())->getJson('/api/products?q=kue')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('products.0.name', 'Kue Lapis');

        $this->actingAs($this->buyer())->getJson('/api/products?q=multwarna')
            ->assertOk()
            ->assertJsonPath('count', 0);

        $this->actingAs($this->buyer())->getJson('/api/products?q=berwarna')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('products.0.name', 'Kue Lapis');
    }

    public function test_api_produk_membatasi_lima_puluh_data(): void
    {
        $seller = $this->seller();

        for ($i = 1; $i <= 55; $i++) {
            $this->produk($seller, 'Produk '.$i);
        }

        $this->actingAs($this->buyer())->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('count', 50);
    }

    public function test_api_pesanan_baru_menampilkan_pesanan_milik_penjual(): void
    {
        $seller = $this->seller();
        $sellerLain = $this->sellerLain();
        $buyer = $this->buyer();

        $produk = $this->produk($seller, 'Kue Lapis');
        $produkLain = $this->produk($sellerLain, 'Kerupuk Udang');

        $order = Order::create(['buyer_id' => $buyer->id, 'product_id' => $produk->id, 'total_price' => 10000]);
        $orderLain = Order::create(['buyer_id' => $buyer->id, 'product_id' => $produkLain->id, 'total_price' => 20000]);

        $this->actingAs($buyer)->getJson('/api/seller/orders/new')->assertForbidden();

        $response = $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id=0');

        $response->assertOk()
            ->assertJsonPath('latest_id', $order->id)
            ->assertJsonPath('orders.0.id', $order->id)
            ->assertJsonPath('orders.0.product_name', 'Kue Lapis')
            ->assertJsonPath('orders.0.buyer_name', 'Budi')
            ->assertJsonPath('orders.0.total_price', 10000)
            ->assertJsonPath('orders.0.total_text', 'Rp10.000')
            ->assertJsonPath('orders.0.status', 'baru');

        $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id='.$order->id)
            ->assertOk()
            ->assertJsonPath('orders', [])
            ->assertJsonPath('latest_id', $order->id);

        $this->actingAs($sellerLain)->getJson('/api/seller/orders/new?after_id=0')
            ->assertOk()
            ->assertJsonPath('orders.0.id', $orderLain->id);
    }

    public function test_katalog_dengan_kata_kunci_menampilkan_hasil(): void
    {
        $seller = $this->seller();
        $produk = $this->produk($seller, 'Kue Lapis', 10000, 'Kue lapis multilayer');

        $this->get('/?q=kue')->assertOk()->assertSee('Kue Lapis');
        $this->get('/cari?q=lapis')->assertOk()->assertSee('Kue Lapis');
        $this->get('/cari?q=tidakada')->assertOk()->assertDontSee('Kue Lapis');
        $this->get('/produk/'.$produk->id)->assertOk()->assertSee('Kue Lapis');
        $this->get('/produk/9999')->assertNotFound();
    }

    public function test_penjual_menyimpan_produk_dengan_pesan_berhasil(): void
    {
        $seller = $this->seller();

        $response = $this->actingAs($seller)->post('/seller/products', [
            'name' => 'Kue Cubit',
            'price' => 15000,
            'description' => 'Kue cubit lembut',
            'audio_description' => 'Kue cubit lembut wangi mentega',
        ]);

        $response->assertRedirect(route('seller.dashboard'))
            ->assertSessionHas('status', 'Produk berhasil disimpan');

        $this->assertDatabaseHas('products', [
            'name' => 'Kue Cubit',
            'price' => 15000,
            'seller_id' => $seller->id,
        ]);
    }

    public function test_validasi_produk_menggunakan_pesan_bahasa_indonesia(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)->post('/seller/products', [
            'name' => str_repeat('a', 121),
            'price' => 0,
            'audio_description' => 'pendek',
        ])->assertSessionHasErrors(['name', 'price', 'audio_description']);

        $this->assertSame(
            'Nama produk maksimal 120 karakter.',
            session('errors')->first('name')
        );
        $this->assertSame(
            'Harga harus lebih besar dari nol.',
            session('errors')->first('price')
        );
        $this->assertSame(
            'Deskripsi audio minimal 10 karakter.',
            session('errors')->first('audio_description')
        );
    }

    public function test_penjual_hanya_melihat_produk_miliknya(): void
    {
        $seller = $this->seller();
        $sellerLain = $this->sellerLain();

        $this->produk($seller, 'Kue Lapis');
        $this->produk($sellerLain, 'Kerupuk Udang');

        $this->actingAs($seller)->get('/seller/products')
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertDontSee('Kerupuk Udang');
    }

    public function test_penjual_hanya_melihat_pesanan_untuk_produk_miliknya(): void
    {
        $seller = $this->seller();
        $sellerLain = $this->sellerLain();
        $buyer = $this->buyer();

        $produk = $this->produk($seller, 'Kue Lapis');
        $produkLain = $this->produk($sellerLain, 'Kerupuk Udang');

        Order::create(['buyer_id' => $buyer->id, 'product_id' => $produk->id, 'total_price' => 10000]);
        Order::create(['buyer_id' => $buyer->id, 'product_id' => $produkLain->id, 'total_price' => 20000]);

        $this->actingAs($seller)->get('/seller/orders')
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertDontSee('Kerupuk Udang');
    }

    public function test_pembeli_memesan_produk_dan_melihat_konfirmasi(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $produk = $this->produk($seller, 'Kue Lapis', 12000);

        $response = $this->actingAs($buyer)->post('/products/'.$produk->id.'/order');

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect('/orders/'.$order->id.'/konfirmasi');
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame($produk->id, $order->product_id);
        $this->assertSame(12000, $order->total_price);
        $this->assertSame('baru', $order->status);

        $this->actingAs($buyer)->get('/orders/'.$order->id.'/konfirmasi')->assertOk();
    }

    public function test_konfirmasi_pesanan_orang_lain_ditolak(): void
    {
        $seller = $this->seller();
        $pemilik = $this->buyer();
        $orangLain = $this->buyer();

        $produk = $this->produk($seller, 'Kue Lapis');
        $order = Order::create(['buyer_id' => $pemilik->id, 'product_id' => $produk->id, 'total_price' => 10000]);

        $this->actingAs($orangLain)->get('/orders/'.$order->id.'/konfirmasi')->assertForbidden();
    }

    public function test_penjual_tidak_boleh_memesan(): void
    {
        $seller = $this->seller();
        $produk = $this->produk($seller, 'Kue Lapis');

        $this->actingAs($seller)->post('/products/'.$produk->id.'/order')->assertForbidden();
    }
}
