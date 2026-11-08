<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tampilan masuk dan daftar yang diambil dari branch frontend.
 */
class AuthTampilanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_halaman_masuk_menampilkan_form_dengan_label_yang_jelas(): void
    {
        $this->get('/masuk')
            ->assertOk()
            ->assertSee('Masuk ke SuaraNiaga')
            ->assertSee('<label for="email"', false)
            ->assertSee('<label for="password"', false)
            ->assertSee('Lompat ke konten utama')
            ->assertSee('Sebutkan email');
    }

    public function test_halaman_masuk_bisa_diakses_lewat_alias_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke SuaraNiaga');
    }

    public function test_halaman_daftar_menampilkan_pilihan_peran_pembeli_dan_penjual(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Daftar akun SuaraNiaga')
            ->assertSee('Pembeli')
            ->assertSee('Penjual')
            ->assertSee('Masuk di sini');
    }

    public function test_halaman_daftar_tidak_menawarkan_peran_admin(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertDontSee('Admin');
    }

    public function test_gagal_masuk_menampilkan_ringkasan_error(): void
    {
        $this->from('/masuk')
            ->post('/masuk', [
                'email' => 'tidak-ada@example.com',
                'password' => 'apapun123',
            ])
            ->assertRedirect('/masuk')
            ->assertSessionHasErrors('email');

        $this->followingRedirects()
            ->post('/masuk', [
                'email' => 'tidak-ada@example.com',
                'password' => 'apapun123',
            ])
            ->assertSee('Periksa kembali isian Anda')
            ->assertSee('role="alert"', false)
            ->assertSee('tabindex="-1"', false);
    }

    public function test_halaman_masuk_menampilkan_pesan_akun_nonaktif(): void
    {
        User::factory()->nonaktif()->create([
            'email' => 'nonaktif@example.com',
            'password' => 'rahasia123',
        ]);

        $this->followingRedirects()
            ->post('/masuk', [
                'email' => 'nonaktif@example.com',
                'password' => 'rahasia123',
            ])
            ->assertSee('Akun Anda dinonaktifkan. Silakan hubungi admin.');
    }
}
