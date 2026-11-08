<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mode Aksesibilitas: tampilan kedua yang dipakai pengguna tunanetra.
 *
 * Tampilan ini memakai satu tindakan per layar, tombol besar, dan navigasi
 * linear. Semua jalur penting harus tetap bisa diselesaikan tanpa suara,
 * memakai ketik biasa saja.
 */
class ModeAksesibilitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function aktifkan(): void
    {
        $this->post('/mode-aksesibilitas/aksesibilitas');
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

    private function produk(User $seller, string $nama = 'Kue Lapis'): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $nama,
            'price' => 15000,
            'stock' => 12,
            'description' => 'Kue lapis multilayer dengan lapisan tipis dan manis',
            'audio_description' => 'Kue lapis multilayer dengan lapisan tipis dan manis',
        ]);
    }

    public function test_mode_normal_tetap_menampilkan_tampilan_awal(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('MODE AKSESIBILITAS');
    }

    public function test_tombol_ganti_menyalakan_mode_aksesibilitas(): void
    {
        $this->aktifkan();

        $this->get('/')
            ->assertOk()
            ->assertSee('MODE AKSESIBILITAS')
            ->assertSee('Selamat datang di SuaraNiaga')
            ->assertSee('Masuk ke akun')
            ->assertSee('Buat akun baru');
    }

    public function test_mode_aksesibilitas_bisa_dimatikan_lagi(): void
    {
        $this->aktifkan();
        $this->post('/mode-aksesibilitas/normal');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('MODE AKSESIBILITAS');
    }

    public function test_beranda_pembeli_menampilkan_lima_tindakan_besar(): void
    {
        $this->aktifkan();

        $this->actingAs($this->buyer())->get('/')
            ->assertOk()
            ->assertSee('Beranda pembeli')
            ->assertSee('Cari produk dengan suara')
            ->assertSee('Dengarkan daftar produk')
            ->assertSee('Keranjang saya')
            ->assertSee('Pesanan saya')
            ->assertSee('Profil dan keluar');
    }

    public function test_login_berlangsung_dua_langkah(): void
    {
        $this->aktifkan();
        $this->buyer();

        $this->get('/masuk')
            ->assertOk()
            ->assertSee('Masuk ke akun')
            ->assertSee('Langkah 1 dari 2');

        $this->post('/masuk/email', ['email' => 'budi@example.com'])
            ->assertRedirect('/masuk/sandi');

        $this->get('/masuk/sandi')
            ->assertOk()
            ->assertSee('Masukkan kata sandi')
            ->assertSee('Langkah 2 dari 2')
            ->assertSee('budi@example.com');

        $this->post('/masuk/sandi', ['password' => 'rahasia123'])
            ->assertRedirect(route('login.masuk.berhasil'));

        $this->assertAuthenticated();
    }

    public function test_login_dengan_kata_sandi_salah_tidak_membuat_sesi(): void
    {
        $this->aktifkan();
        $this->buyer();

        $this->post('/masuk/email', ['email' => 'budi@example.com']);
        $this->post('/masuk/sandi', ['password' => 'salah'])
            ->assertRedirect('/masuk/sandi')
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_pendaftaran_berlangsung_satu_kolom_pertahap(): void
    {
        $this->aktifkan();

        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Siapa nama lengkap Anda?');

        $this->post('/daftar/nama', ['name' => 'Budi'])
            ->assertRedirect(route('daftar.langkah', 'email'));

        $this->post('/daftar/email', ['email' => 'budi@example.com'])
            ->assertRedirect(route('daftar.langkah', 'telepon'));

        $this->post('/daftar/telepon', ['phone' => '08123456789'])
            ->assertRedirect(route('daftar.langkah', 'alamat'));

        $this->post('/daftar/alamat', ['address' => 'Jl. Merdeka 10, Bandung'])
            ->assertRedirect(route('daftar.langkah', 'sandi'));

        $this->post('/daftar/sandi', ['password' => 'rahasia123'])
            ->assertRedirect(route('daftar.langkah', 'konfirmasi'));

        $this->post('/daftar/konfirmasi', ['password_confirmation' => 'rahasia123'])
            ->assertRedirect(route('daftar.langkah', 'peran'));

        $this->get(route('daftar.langkah', 'peran'))
            ->assertOk()
            ->assertSee('Pembeli')
            ->assertSee('Penjual');

        $this->post('/daftar/selesai', ['role' => 'seller'])
            ->assertRedirect(route('daftar.berhasil'));

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'role' => User::ROLE_SELLER,
        ]);
        $this->assertAuthenticated();
    }

    public function test_konfirmasi_sandi_yang_tidak_cocok_menolak_langkah(): void
    {
        $this->aktifkan();

        $this->post('/daftar/nama', ['name' => 'Budi']);
        $this->post('/daftar/email', ['email' => 'budi@example.com']);
        $this->post('/daftar/telepon', ['phone' => '08123456789']);
        $this->post('/daftar/alamat', ['address' => 'Jl. Merdeka 10, Bandung']);
        $this->post('/daftar/sandi', ['password' => 'rahasia123']);

        $this->post('/daftar/konfirmasi', ['password_confirmation' => 'beda123'])
            ->assertSessionHasErrors('password_confirmation');

        $this->assertGuest();
    }

    public function test_halaman_pencarian_memiliki_alternatif_ketik_biasa(): void
    {
        $this->aktifkan();

        $this->get('/cari')
            ->assertOk()
            ->assertSee('Mulai dengarkan pencarian suara')
            ->assertSee('Atau ketik nama produk')
            ->assertSee('type="search"', false);
    }

    public function test_produk_ditampilkan_satu_per_layar_dengan_tombol_dengarkan(): void
    {
        $this->aktifkan();
        $seller = $this->seller();
        $this->produk($seller, 'Kue Lapis');
        $this->produk($seller, 'Beras Ramos');

        $pertama = $this->get('/daftar-produk')->assertOk()->getContent();
        $kedua = $this->get('/daftar-produk?halaman=2')->assertOk()->getContent();

        foreach ([$pertama, $kedua] as $halaman) {
            $this->assertStringContainsString('Dengarkan deskripsi', $halaman);
            $this->assertStringContainsString('Masukkan ke keranjang', $halaman);
            $this->assertStringContainsString('Beli produk', $halaman);
        }

        // Satu produk per layar: kedua nama tidak pernah muncul di halaman sama.
        $this->assertNotSame(
            str_contains($pertama, 'Kue Lapis'),
            str_contains($pertama, 'Beras Ramos')
        );
        $this->assertNotSame(
            str_contains($kedua, 'Kue Lapis'),
            str_contains($kedua, 'Beras Ramos')
        );
        $this->assertTrue(
            (str_contains($pertama, 'Kue Lapis') && str_contains($kedua, 'Beras Ramos'))
            || (str_contains($pertama, 'Beras Ramos') && str_contains($kedua, 'Kue Lapis'))
        );
    }

    public function test_keranjang_menampilkan_satu_barang_per_layar(): void
    {
        $this->aktifkan();
        $buyer = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($buyer)->post('/keranjang/'.$produk->id);

        $this->actingAs($buyer)->get('/keranjang')
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('Kurangi jumlah')
            ->assertSee('Tambah jumlah')
            ->assertSee('Beli produk');

        $this->actingAs($buyer)->patch('/keranjang/'.$produk->id, ['jumlah' => 3])
            ->assertRedirect(route('keranjang'));

        $this->actingAs($buyer)->get('/keranjang')
            ->assertOk()
            ->assertSee('Jumlah: 3');
    }

    public function test_checkout_menampilkan_ringkasan_dan_alamat(): void
    {
        $this->aktifkan();
        $buyer = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($buyer)->post('/keranjang/'.$produk->id);

        $this->actingAs($buyer)->get('/checkout')
            ->assertOk()
            ->assertSee('RINGKASAN PRODUK')
            ->assertSee('ALAMAT PENGIRIMAN')
            ->assertSee('METODE PEMBAYARAN')
            ->assertSee('Konfirmasi pesanan');
    }

    public function test_pesanan_menampilkan_linimasa_status(): void
    {
        $this->aktifkan();
        $buyer = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $this->actingAs($buyer)->get('/pesanan')
            ->assertOk()
            ->assertSee('Kue Lapis')
            ->assertSee('Dengarkan detail pesanan')
            ->assertSee('Konfirmasi diterima');
    }

    public function test_pembeli_bisa_menandai_pesanan_diterima(): void
    {
        $this->aktifkan();
        $buyer = $this->buyer();
        $produk = $this->produk($this->seller());

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $order = Order::firstOrFail();
        $order->update(['status' => Order::STATUS_DIKIRIM]);

        $this->actingAs($buyer)->patch('/pesanan/'.$order->id.'/terima')
            ->assertRedirect();

        $this->assertSame(Order::STATUS_SELESAI, $order->fresh()->status);
    }

    public function test_profil_dan_konfirmasi_keluar_tersedia_di_mode_aksesibilitas(): void
    {
        $this->aktifkan();
        $buyer = $this->buyer();

        $this->actingAs($buyer)->get('/profil')
            ->assertOk()
            ->assertSee('Profil dan keluar')
            ->assertSee('budi@example.com')
            ->assertSee('Keluar dari akun');

        $this->actingAs($buyer)->get('/keluar')
            ->assertOk()
            ->assertSee('Keluar dari akun')
            ->assertSee('Ya, keluar dari akun');

        $this->actingAs($buyer)->post('/keluar')->assertRedirect(route('beranda'));

        $this->assertGuest();
    }

    public function test_beranda_penjual_menampilkan_lima_tindakan_besar(): void
    {
        $this->aktifkan();

        $this->actingAs($this->seller())->get('/penjual')
            ->assertOk()
            ->assertSee('Beranda penjual')
            ->assertSee('Tambah produk dengan suara')
            ->assertSee('Produk saya')
            ->assertSee('Pesanan masuk')
            ->assertSee('Status pesanan')
            ->assertSee('Profil toko dan keluar');
    }

    public function test_penjual_menambah_produk_langkah_demi_langkah(): void
    {
        $this->aktifkan();
        $seller = $this->seller();

        $this->actingAs($seller)->get('/penjual/produk/tambah')
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'nama'));

        $this->actingAs($seller)->get(route('penjual.produk.tambah.langkah', 'nama'))
            ->assertOk()
            ->assertSee('Sebutkan nama produk')
            ->assertSee('Mulai dengarkan')
            ->assertSee('Lanjut ke informasi harga');

        $this->actingAs($seller)->post('/penjual/produk/tambah/nama', ['name' => 'Beras Ramos'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'harga'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/harga', ['price' => '72 ribu'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'stok'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/stok', ['stock' => '24'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'deskripsi'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/deskripsi', [
            'description' => 'Beras pulen dengan tekstur lembut dan kemasan tertutup',
        ])->assertRedirect(route('penjual.produk.tambah.langkah', 'simpan'));

        $this->actingAs($seller)->get(route('penjual.produk.tambah.langkah', 'simpan'))
            ->assertOk()
            ->assertSee('Periksa dan simpan produk')
            ->assertSee('Simpan produk');

        $this->actingAs($seller)->post('/penjual/produk', [
            'name' => 'Beras Ramos',
            'price' => 72000,
            'stock' => 24,
            'description' => 'Beras pulen dengan tekstur lembut dan kemasan tertutup',
            'audio_description' => 'Beras pulen dengan tekstur lembut dan kemasan tertutup',
        ])->assertRedirect(route('penjual.produk'));

        $this->assertDatabaseHas('products', [
            'name' => 'Beras Ramos',
            'price' => 72000,
            'stock' => 24,
            'seller_id' => $seller->id,
        ]);
    }

    public function test_ubah_produk_memperbarui_produk_yang_ada_bukan_membuat_duplikat(): void
    {
        $this->aktifkan();
        $seller = $this->seller();
        $produk = $this->produk($seller, 'Kue Lapis');

        $this->actingAs($seller)->get('/penjual/produk/'.$produk->id.'/ubah')
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'nama'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/nama', ['name' => 'Kue Lapis Royal'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'harga'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/harga', ['price' => '25000'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'stok'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/stok', ['stock' => '9'])
            ->assertRedirect(route('penjual.produk.tambah.langkah', 'deskripsi'));

        $this->actingAs($seller)->post('/penjual/produk/tambah/deskripsi', [
            'description' => 'Kue lapis royal dengan lapisan tebal dan warna cerah',
        ])->assertRedirect(route('penjual.produk.tambah.langkah', 'simpan'));

        $this->actingAs($seller)->post('/penjual/produk', [
            'produk_id' => $produk->id,
            'name' => 'Kue Lapis Royal',
            'price' => 25000,
            'stock' => 9,
            'description' => 'Kue lapis royal dengan lapisan tebal dan warna cerah',
            'audio_description' => 'Kue lapis royal dengan lapisan tebal dan warna cerah',
        ])->assertRedirect(route('penjual.produk'));

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', [
            'id' => $produk->id,
            'name' => 'Kue Lapis Royal',
            'price' => 25000,
            'stock' => 9,
        ]);
    }

    public function test_penjual_menerima_pesanan_dan_menaikkan_status(): void
    {
        $this->aktifkan();
        $seller = $this->seller();
        $buyer = $this->buyer();
        $produk = $this->produk($seller);

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $order = Order::firstOrFail();

        $this->actingAs($seller)->get('/penjual/pesanan')
            ->assertOk()
            ->assertSee('Terima pesanan')
            ->assertSee('Tolak pesanan');

        $this->actingAs($seller)->patch('/penjual/pesanan/'.$order->id.'/status', ['status' => Order::STATUS_DIPROSES])
            ->assertRedirect();

        $this->assertSame(Order::STATUS_DIPROSES, $order->fresh()->status);
    }

    public function test_status_pesanan_penjual_menampilkan_langkah_berikutnya(): void
    {
        $this->aktifkan();
        $seller = $this->seller();
        $buyer = $this->buyer();
        $produk = $this->produk($seller);

        $this->actingAs($buyer)->post('/produk/'.$produk->id.'/order');

        $this->actingAs($seller)->get('/penjual/status')
            ->assertOk()
            ->assertSee('Tandai sedang dikirim');
    }
}
