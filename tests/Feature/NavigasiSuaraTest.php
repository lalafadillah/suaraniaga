<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Navigasi suara global.
 *
 * Nama dan jenis elemen interaktif dibacakan otomatis memakai Web Speech API
 * ketika pointer melewati elemen atau fokus berpindah. Modulnya ada di
 * resources/js/voice/navigasi.js dan dimuat dari resources/js/app.js, jadi
 * semua tampilan memakai sistem yang sama.
 *
 * Pembacaan otomatis mengikuti Mode Aksesibilitas. Atribut
 * data-mode-aksesibilitas pada elemen <html> yang meneruskan status mode ke
 * lapisan peramban.
 */
class NavigasiSuaraTest extends TestCase
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

    private function admin(): User
    {
        return User::create([
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_ADMIN,
        ]);
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

    public function test_modul_navigasi_suara_dimuat_dari_app_js(): void
    {
        $modul = base_path('resources/js/voice/navigasi.js');
        $app = base_path('resources/js/app.js');

        $this->assertFileExists($modul);
        $this->assertStringContainsString('voice/navigasi.js', file_get_contents($app));
        $this->assertStringContainsString('initNavigasiSuara', file_get_contents($app));
    }

    public function test_halaman_umum_menandai_mode_aksesibilitas_nonaktif(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-mode-aksesibilitas="0"', false);
    }

    public function test_mode_aksesibilitas_aktif_menandai_pembacaan_otomatis(): void
    {
        $this->aktifkan();

        $this->get('/')
            ->assertOk()
            ->assertSee('data-mode-aksesibilitas="1"', false);
    }

    public function test_semua_layout_memakai_sistem_navigasi_suara_yang_sama(): void
    {
        $this->aktifkan();

        $halaman = [
            '/masuk',
            '/daftar',
            '/pengaturan',
        ];

        foreach ($halaman as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-mode-aksesibilitas="1"', false);
        }
    }

    public function test_halaman_penjual_ikut_menandai_mode_aksesibilitas(): void
    {
        $this->aktifkan();

        $this->actingAs($this->seller())
            ->get('/penjual')
            ->assertOk()
            ->assertSee('data-mode-aksesibilitas="1"', false);
    }

    public function test_halaman_admin_ikut_menandai_mode_aksesibilitas(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('data-mode-aksesibilitas="0"', false);

        $this->aktifkan();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('data-mode-aksesibilitas="1"', false);
    }

    public function test_halaman_galat_403_ikut_memuat_navigasi_suara(): void
    {
        $pembeli = User::create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_BUYER,
        ]);

        $this->actingAs($pembeli)
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee('data-mode-aksesibilitas=', false);
    }

    public function test_pengaturan_memuat_kontrol_pembacaan_navigasi(): void
    {
        $this->get('/pengaturan')
            ->assertOk()
            ->assertSee('Pembacaan navigasi')
            ->assertSee('Ikuti mode aksesibilitas');
    }

    public function test_bantuan_aksesibilitas_memuat_kontrol_pembacaan_navigasi(): void
    {
        $this->aktifkan();

        $this->get('/pengaturan')
            ->assertOk()
            ->assertSee('Pembacaan navigasi')
            ->assertSee('voiceNavigation');
    }
}
