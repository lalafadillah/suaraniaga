<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pelengkap ProdukPesananApiTest: validasi simpan produk, sumber seller_id,
 * dan kecocokan persis API produk dengan kontrak.
 */
class ProdukTest extends TestCase
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

    private function sellerLain(): User
    {
        return User::create([
            'name' => 'Tono',
            'email' => 'tono@example.com',
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

    public function test_seller_id_diambil_dari_pengguna_yang_masuk(): void
    {
        $seller = $this->seller();
        $sellerLain = $this->sellerLain();

        $this->actingAs($seller)->post('/seller/products', [
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
            'seller_id' => $sellerLain->id,
        ])->assertRedirect(route('seller.dashboard'));

        $produk = Product::firstOrFail();

        $this->assertSame($seller->id, $produk->seller_id);
        $this->assertNotSame($sellerLain->id, $produk->seller_id);
    }

    public function test_validasi_gagal_untuk_data_produk_tidak_lengkap(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)->post('/seller/products', [
            'name' => '',
            'price' => 0,
            'audio_description' => '',
        ])->assertSessionHasErrors(['name', 'price', 'audio_description']);

        $this->actingAs($seller)->post('/seller/products', [
            'name' => 'Kue Lapis',
            'price' => -5000,
            'audio_description' => 'Kue lapis multilayer',
        ])->assertSessionHasErrors('price');

        $this->actingAs($seller)->post('/seller/products', [
            'name' => 'Kue Lapis',
            'price' => 'bukan-angka',
            'audio_description' => 'Kue lapis multilayer',
        ])->assertSessionHasErrors('price');

        $this->actingAs($seller)->post('/seller/products', [
            'name' => 'Kue Lapis',
            'price' => 10000,
            'audio_description' => 'pendek',
        ])->assertSessionHasErrors('audio_description');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_api_produk_cocok_persis_dengan_kontrak(): void
    {
        $seller = $this->seller();

        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);

        $this->actingAs($this->buyer())->getJson('/api/products')
            ->assertOk()
            ->assertExactJson([
                'count' => 1,
                'products' => [
                    [
                        'id' => 1,
                        'name' => 'Kue Lapis',
                        'price' => 10000,
                        'price_text' => 'Rp10.000',
                        'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
                        'url' => '/products/1',
                    ],
                ],
            ]);
    }

    public function test_halaman_cari_hanya_menampilkan_produk_yang_cocok(): void
    {
        $seller = $this->seller();

        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);

        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kerupuk Udang',
            'price' => 20000,
            'description' => 'Kerupuk gurih',
            'audio_description' => 'Kerupuk udang yang renyah',
        ]);

        $this->get('/?q=kue')->assertOk()
            ->assertSee('Kue Lapis')
            ->assertDontSee('Kerupuk Udang');

        $this->get('/cari?q=gurih')->assertOk()
            ->assertSee('Kerupuk Udang')
            ->assertDontSee('Kue Lapis');

        $this->get('/cari')->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('Kerupuk Udang');

        $this->get('/')->assertOk();
    }

    public function test_api_produk_tanpa_kata_kunci_mengembalikan_semua(): void
    {
        $seller = $this->seller();

        foreach (['Kue Lapis', 'Kerupuk Udang', 'Minyak Goreng'] as $nama) {
            Product::create([
                'seller_id' => $seller->id,
                'name' => $nama,
                'price' => 10000,
                'description' => 'Deskripsi '.$nama,
                'audio_description' => 'Deskripsi audio '.$nama,
            ]);
        }

        $this->actingAs($this->buyer())->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('count', 3);
    }
}
