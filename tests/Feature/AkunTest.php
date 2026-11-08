<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * CRUD data akun: masuk, baca profil, ubah data, ubah sandi, hapus akun, keluar.
 */
class AkunTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private int $sequence = 0;

    private function buyer(): User
    {
        return User::factory()->create([
            'name' => 'Budi',
            'email' => 'budi+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
        ]);
    }

    public function test_daftar_membuat_akun_dan_langsung_masuk(): void
    {
        $this->post('/daftar', [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'seller',
        ])->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'rani@example.com', 'role' => 'seller']);
        $this->assertAuthenticated();
    }

    public function test_daftar_tidak_bisa_memilih_peran_admin(): void
    {
        $this->post('/daftar', [
            'name' => 'Calon Admin',
            'email' => 'calon@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'calon@example.com']);
    }

    public function test_masuk_dan_keluar_menggunakan_sesi(): void
    {
        $user = $this->buyer();

        $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertRedirect(route('beranda'));

        $this->assertAuthenticatedAs($user);

        $this->post('/keluar')->assertRedirect(route('beranda'));

        $this->assertGuest();
    }

    public function test_masuk_gagal_memberi_pesan_bahasa_indonesia(): void
    {
        $user = $this->buyer();

        $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_halaman_akun_menampilkan_data_pengguna(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->get('/akun')
            ->assertOk()
            ->assertSee('Akun saya')
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('Pembeli');
    }

    public function test_halaman_akun_menuntut_login(): void
    {
        $this->get('/akun')->assertRedirect(route('login'));
        $this->get('/profil')->assertRedirect(route('login'));
    }

    public function test_memperbarui_nama_dan_email(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->patch('/akun', [
            'name' => 'Budi Santoso',
            'email' => 'budi.baru@example.com',
        ])->assertRedirect(route('akun'))
            ->assertSessionHas('status', 'Data akun berhasil diperbarui.');

        $user->refresh();

        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('budi.baru@example.com', $user->email);
    }

    public function test_email_yang_dipakai_akun_lain_ditolak(): void
    {
        $user = $this->buyer();
        $lain = $this->buyer();

        $this->actingAs($user)->patch('/akun', [
            'name' => $user->name,
            'email' => $lain->email,
        ])->assertSessionHasErrors('email');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_mengganti_kata_sandi_dengan_kata_sandi_lama_yang_benar(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->patch('/akun/sandi', [
            'current_password' => 'rahasia123',
            'password' => 'katasandibaru123',
            'password_confirmation' => 'katasandibaru123',
        ])->assertRedirect(route('akun'))
            ->assertSessionHas('status', 'Kata sandi berhasil diperbarui.');

        $this->assertTrue(Hash::check('katasandibaru123', $user->fresh()->password));
    }

    public function test_ganti_kata_sandi_ditolak_bila_kata_sandi_lama_salah(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->patch('/akun/sandi', [
            'current_password' => 'salah',
            'password' => 'katasandibaru123',
            'password_confirmation' => 'katasandibaru123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
    }

    public function test_menghapus_akun_sendiri(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->delete('/akun', [
            'current_password' => 'rahasia123',
        ])->assertRedirect(route('beranda'))
            ->assertSessionHas('status', 'Akun Anda sudah dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertGuest();
    }

    public function test_hapus_akun_ditolak_bila_kata_sandi_salah(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)->delete('/akun', [
            'current_password' => 'salah',
        ])->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertAuthenticated();
    }

    public function test_menghapus_akun_penjual_juga_menghapus_produk_dan_pesanan(): void
    {
        $seller = User::factory()->create([
            'email' => 'penjual@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);

        $buyer = $this->buyer();

        $produk = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);

        Order::create([
            'buyer_id' => $buyer->id,
            'product_id' => $produk->id,
            'total_price' => 10000,
        ]);

        $this->actingAs($seller)->delete('/akun', [
            'current_password' => 'rahasia123',
        ])->assertRedirect(route('beranda'));

        $this->assertDatabaseMissing('users', ['id' => $seller->id]);
        $this->assertDatabaseMissing('products', ['id' => $produk->id]);
        $this->assertDatabaseCount('orders', 0);
    }
}
