<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContractSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

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

    private function buyer(): User
    {
        return User::create([
            'name' => 'Budi',
            'email' => 'budi+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);
    }

    private function product(User $seller): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);
    }

    public function test_katalog_dan_detail_produk_tampil(): void
    {
        $product = $this->product($this->seller());

        $this->get('/')->assertOk();
        $this->get('/produk/'.$product->id)->assertOk();
        $this->get('/cari?q=kue')->assertOk();
    }

    public function test_pendaftaran_pembeli_membuat_sesi_dan_mengarah_ke_beranda(): void
    {
        $this->post('/register', [
            'name' => 'Dina',
            'email' => 'dina@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'buyer',
        ])->assertRedirect(route('beranda'));

        $this->assertDatabaseHas('users', ['email' => 'dina@example.com', 'role' => 'buyer']);
        $this->assertAuthenticated();
    }

    public function test_pendaftaran_penjual_mengarah_ke_dasbor_penjual(): void
    {
        $this->post('/register', [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'seller',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'rani@example.com', 'role' => 'seller']);
    }

    public function test_kata_sandi_disimpan_sebagai_hash(): void
    {
        $this->post('/register', [
            'name' => 'Dina',
            'email' => 'dina@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'buyer',
        ])->assertRedirect();

        $user = User::where('email', 'dina@example.com')->firstOrFail();

        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_validasi_pendaftaran_memakai_pesan_bahasa_indonesia(): void
    {
        $this->post('/register', [
            'name' => '',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'lain',
            'role' => 'buyer',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertSame(
            'Nama wajib diisi.',
            session('errors')->first('name')
        );
        $this->assertSame(
            'Format email tidak valid.',
            session('errors')->first('email')
        );
    }

    public function test_masuk_gagal_memberi_pesan_salah_dan_menyimpan_email(): void
    {
        $this->buyer();

        $this->post('/login', [
            'email' => 'budi+1@example.com',
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_masuk_mengarah_ke_halaman_sesuai_peran(): void
    {
        $this->buyer();
        $this->seller();

        $this->post('/login', [
            'email' => 'budi+1@example.com',
            'password' => 'rahasia123',
        ])->assertRedirect(route('beranda'));

        $this->post('/logout')->assertRedirect(route('beranda'));

        $this->post('/login', [
            'email' => 'sari+2@example.com',
            'password' => 'rahasia123',
        ])->assertRedirect(route('seller.dashboard'));
    }

    public function test_keluar_menghapus_sesi(): void
    {
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post('/logout')->assertRedirect(route('beranda'));

        $this->assertGuest();
    }

    public function test_halaman_penjual_hanya_untuk_peran_seller(): void
    {
        $this->actingAs($this->buyer())->get('/seller/dashboard')->assertForbidden();
        $this->actingAs($this->seller())->get('/seller/dashboard')->assertOk();
        $this->actingAs($this->seller())->get('/seller/products/create')->assertOk();
    }

    public function test_halaman_pembeli_hanya_untuk_peran_buyer(): void
    {
        $this->actingAs($this->seller())->get('/keranjang')->assertForbidden();
        $this->actingAs($this->buyer())->get('/keranjang')->assertOk();
    }

    public function test_halaman_tamu_dapat_diakses(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_halaman_penjual_menuntut_login(): void
    {
        $this->get('/seller/dashboard')->assertRedirect(route('login'));
    }

    public function test_pemesanan_membuat_order_dan_konfirmasi(): void
    {
        $product = $this->product($this->seller());
        $buyer = $this->buyer();

        $response = $this->actingAs($buyer)->post('/produk/'.$product->id.'/order');

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect('/orders/'.$order->id.'/konfirmasi');
        $this->assertSame(10000, $order->total_price);
        $this->assertSame('baru', $order->status);

        $this->actingAs($buyer)->get('/orders/'.$order->id.'/konfirmasi')->assertOk();
    }

    public function test_penjual_bisa_menyimpan_produk_dan_mengubah_status_pesanan(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller);
        $buyer = $this->buyer();
        $order = Order::create(['buyer_id' => $buyer->id, 'product_id' => $product->id, 'total_price' => $product->price]);

        $this->actingAs($seller)->post('/penjual/produk', [
            'name' => 'Kue Cubit',
            'price' => 15000,
            'description' => 'Kue cubit lembut',
            'audio_description' => 'Kue cubit lembut wangi mentega',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('products', ['name' => 'Kue Cubit', 'seller_id' => $seller->id]);

        $this->actingAs($seller)
            ->patch('/penjual/pesanan/'.$order->id.'/status', ['status' => 'diproses'])
            ->assertRedirect();

        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_api_produk_dan_pesanan_baru_mengikuti_kontrak(): void
    {
        $seller = $this->seller();
        $this->product($seller);

        $response = $this->actingAs($this->buyer())->getJson('/api/products?q=kue');

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('products.0.name', 'Kue Lapis')
            ->assertJsonPath('products.0.price', 10000)
            ->assertJsonPath('products.0.price_text', 'Rp10.000')
            ->assertJsonPath('products.0.url', '/products/1');

        $this->actingAs($this->buyer())->getJson('/api/seller/orders/new')->assertForbidden();

        $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id=0')
            ->assertOk()
            ->assertJsonPath('latest_id', 0)
            ->assertJsonPath('orders', []);
    }
}
