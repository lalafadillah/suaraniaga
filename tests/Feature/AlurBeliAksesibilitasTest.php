<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\ModeAksesibilitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur Mode Aksesibilitas: pembeli memilih peran, membeli produk, lalu
 * penjual toko yang sama melihat pesanan itu. Setiap akun hanya melihat
 * miliknya sendiri.
 */
class AlurBeliAksesibilitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function seller(string $nama = 'Sari'): User
    {
        return User::create([
            'name' => $nama,
            'email' => strtolower($nama).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    private function buyer(string $nama = 'Budi'): User
    {
        return User::create([
            'name' => $nama,
            'email' => strtolower($nama).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);
    }

    private function produk(User $penjual, string $nama = 'Kue Lapis'): Product
    {
        return Product::create([
            'seller_id' => $penjual->id,
            'name' => $nama,
            'price' => 15000,
            'stock' => 12,
            'description' => 'Kue lapis multilayer dengan lapisan tipis dan manis',
            'audio_description' => 'Kue lapis multilayer dengan lapisan tipis dan manis',
        ]);
    }

    public function test_layout_aksesibilitas_punya_akar_x_data_supaya_tombol_suara_hidup(): void
    {
        $penjual = $this->seller();
        $this->produk($penjual);

        $html = $this->withSession([ModeAksesibilitas::KUNCI_SESI => true])
            ->get('/daftar-produk')
            ->assertOk()
            ->getContent();

        // Alpine hanya menginisialisasi elemen yang punya x-data sebagai akar.
        $this->assertMatchesRegularExpression(
            '/class="[^"]*min-h-screen[^"]*"\s+x-data/',
            $html,
            'Layout aksesibilitas harus punya pembungkus dengan x-data.'
        );

        $this->assertStringContainsString('x-on:click', $html);
    }

    public function test_kedua_pilihan_peran_bisa_dicapai_dengan_tombol_tab(): void
    {
        $html = $this->withSession([ModeAksesibilitas::KUNCI_SESI => true])
            ->get('/daftar/peran')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/value="buyer"[^>]*tabindex="0"/', $html);
        $this->assertMatchesRegularExpression('/value="seller"[^>]*tabindex="0"/', $html);
    }

    public function test_halaman_daftar_produk_punya_tombol_beranda_dan_dengarkan_deskripsi(): void
    {
        $penjual = $this->seller();
        $produk = $this->produk($penjual);

        $respons = $this->withSession([ModeAksesibilitas::KUNCI_SESI => true])
            ->get('/daftar-produk')
            ->assertOk();

        $respons->assertSee('Dengarkan daftar produk');
        $respons->assertSee('Kembali ke beranda');
        $respons->assertSee('Dengarkan deskripsi');
        $respons->assertSee('Masukkan ke keranjang');
        $respons->assertSee('Beli produk');

        // Deskripsi produk ikut dibacakan, bukan hanya nama produk.
        $html = $respons->getContent();
        $this->assertStringContainsString($produk->description, $html);
        $this->assertStringContainsString('speak(', $html);
    }

    public function test_masukkan_ke_keranjang_berakhir_di_halaman_keranjang(): void
    {
        $pembeli = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($pembeli)
            ->post('/keranjang/'.$produk->id)
            ->assertRedirect(route('keranjang'));

        $this->assertSame([$produk->id => 1], session('keranjang'));
    }

    public function test_beli_produk_berakhir_di_halaman_checkout(): void
    {
        $pembeli = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($pembeli)
            ->post('/produk/'.$produk->id.'/beli')
            ->assertRedirect(route('checkout'));

        $this->assertSame([$produk->id => 1], session('keranjang'));
    }

    public function test_beli_produk_menolak_akun_non_pembeli(): void
    {
        $penjual = $this->seller();
        $produk = $this->produk($penjual);

        $this->actingAs($penjual)
            ->post('/produk/'.$produk->id.'/beli')
            ->assertForbidden();
    }

    public function test_checkout_membuat_pesanan_yang_lihat_penjual_toko_yang_sama(): void
    {
        $penjual = $this->seller('Sari');
        $pembeli = $this->buyer('Budi');
        $produk = $this->produk($penjual);

        $this->actingAs($pembeli)
            ->post('/keranjang/'.$produk->id);

        $this->actingAs($pembeli)
            ->post('/checkout', [
                'nama' => 'Budi Pembeli',
                'telepon' => '08123456789',
                'alamat' => 'Jl. Melati No. 5, Bandung',
                'metode' => 'simulasi',
            ])
            ->assertRedirect(route('pesanan.sukses'));

        $pesanan = Order::query()->where('buyer_id', $pembeli->id)->first();

        $this->assertNotNull($pesanan);
        $this->assertSame($produk->id, $pesanan->product_id);
        $this->assertSame('Budi Pembeli', $pesanan->recipient_name);

        // Penjual toko itu melihat pesanan beserta nama pembeli.
        $this->actingAs($penjual)
            ->get('/penjual/pesanan')
            ->assertOk()
            ->assertSee('Budi Pembeli')
            ->assertSee($produk->name);

        // Di Mode Aksesibilitas nama pembeli dibacakan lewat label yang jelas.
        $this->actingAs($penjual)
            ->withSession([ModeAksesibilitas::KUNCI_SESI => true])
            ->get('/penjual/pesanan')
            ->assertOk()
            ->assertSee('Pembeli: Budi')
            ->assertSee('Penerima: Budi Pembeli');

        // Penjual lain tidak boleh melihat pesanan tersebut.
        $penjualLain = $this->seller('Rani');

        $this->actingAs($penjualLain)
            ->get('/penjual/pesanan')
            ->assertOk()
            ->assertSee('Belum ada pesanan masuk')
            ->assertDontSee('Budi Pembeli');
    }

    public function test_pembeli_tidak_bisa_membuka_halaman_pesanan_penjual(): void
    {
        $pembeli = $this->buyer();

        $this->actingAs($pembeli)
            ->get('/penjual/pesanan')
            ->assertForbidden();
    }

    public function test_pesanan_yang_dibuat_terlihat_di_halaman_pesanan_saya_pembeli(): void
    {
        $penjual = $this->seller();
        $pembeli = $this->buyer('Budi');
        $produk = $this->produk($penjual);

        $this->actingAs($pembeli)->post('/keranjang/'.$produk->id);
        $this->actingAs($pembeli)->post('/checkout', [
            'nama' => 'Budi Pembeli',
            'telepon' => '08123456789',
            'alamat' => 'Jl. Melati No. 5, Bandung',
            'metode' => 'simulasi',
        ]);

        $this->actingAs($pembeli)
            ->get('/pesanan')
            ->assertOk()
            ->assertSee($produk->name);
    }
}
