<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pelengkap ContractSmokeTest: kasus autentikasi dan penjagaan peran yang
 * belum diperiksa di sana.
 */
class AuthPeranTest extends TestCase
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
        return User::create([
            'name' => 'Budi',
            'email' => 'budi+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);
    }

    private function seller(): User
    {
        return User::create([
            'name' => 'Sari',
            'email' => 'sari+'.(++$this->sequence).'@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    public function test_login_dengan_password_salah_memberi_pesan_bahasa_indonesia(): void
    {
        $buyer = $this->buyer();

        $this->post('/login', [
            'email' => $buyer->email,
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors('email');

        $this->assertSame('Email atau kata sandi salah.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_pembeli_dilarang_membuka_halaman_penjual(): void
    {
        $this->actingAs($this->buyer())->get('/seller/products/create')->assertForbidden();
        $this->actingAs($this->buyer())->get('/seller/products')->assertForbidden();
        $this->actingAs($this->buyer())->get('/seller/orders')->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/keranjang')->assertRedirect(route('login'));
        $this->get('/seller/products')->assertRedirect(route('login'));
        $this->get('/orders/1/konfirmasi')->assertRedirect(route('login'));
    }

    public function test_peran_di_luar_daftar_ditolak_saat_pendaftaran(): void
    {
        $this->post('/register', [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'rani@example.com']);
    }

    public function test_peran_tidak_bisa_ditempel_lewat_mass_assignment(): void
    {
        $this->post('/register', [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'seller',
        ])->assertRedirect(route('seller.dashboard'));

        $user = User::where('email', 'rani@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_SELLER, $user->role);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertNotSame('rahasia123', $user->password);
    }

    public function test_halaman_memakai_bahasa_indonesia(): void
    {
        $this->get('/')->assertOk()->assertSee('<html lang="id"', false);
        $this->get('/cari')->assertOk()->assertSee('<html lang="id"', false);
    }
}
