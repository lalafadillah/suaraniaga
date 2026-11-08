<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Tes peran admin: akses halaman, pengelolaan akun, akun nonaktif,
 * dan pesan penolakan akses.
 */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'name' => 'Admin SuaraNiaga',
            'email' => 'admin@example.com',
            'password' => 'rahasia123',
        ]);
    }

    private function buyer(): User
    {
        return User::factory()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
        ]);
    }

    private function seller(): User
    {
        return User::factory()->create([
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_SELLER,
        ]);
    }

    public function test_admin_masuk_diarahkan_ke_dasbor_admin(): void
    {
        $this->admin();

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_bisa_membuka_halaman_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Dasbor admin');
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('Kelola akun pengguna');
        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee('Tinjau produk');
        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee('Pantau pesanan');
    }

    public function test_dasbor_admin_menampilkan_jumlah_data(): void
    {
        $admin = $this->admin();
        $buyer = $this->buyer();
        $seller = $this->seller();

        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);

        Order::create([
            'buyer_id' => $buyer->id,
            'product_id' => Product::first()->id,
            'total_price' => 10000,
        ]);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Pembeli')
            ->assertSee('Penjual')
            ->assertSee('Produk terdaftar')
            ->assertSee('Pesanan masuk')
            ->assertSee('1 pesanan');
    }

    public function test_pembeli_dan_penjual_tidak_bisa_membuka_halaman_admin(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller();

        $this->actingAs($buyer)->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee('Akses ditolak. Halaman ini khusus admin.');

        $this->actingAs($seller)->get('/admin/users')
            ->assertForbidden()
            ->assertSee('Akses ditolak. Halaman ini khusus admin.');
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
    }

    public function test_admin_menonaktifkan_akun_pembeli(): void
    {
        $admin = $this->admin();
        $buyer = $this->buyer();

        $this->actingAs($admin)
            ->patch('/admin/users/'.$buyer->id.'/status')
            ->assertRedirect()
            ->assertSessionHas('status', 'Akun Budi berhasil dinonaktifkan.');

        $this->assertFalse($buyer->fresh()->is_active);
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $admin = $this->admin();
        $buyer = $this->buyer();

        $this->actingAs($admin)->patch('/admin/users/'.$buyer->id.'/status');

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(
            'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            session('errors')->first('email')
        );

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_langsung_keluar_dari_sesi(): void
    {
        $admin = $this->admin();
        $buyer = $this->buyer();

        $this->actingAs($buyer);

        $buyer->is_active = false;
        $buyer->save();

        $this->get('/')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_tidak_bisa_menonaktifkan_diri_sendiri_atau_admin_lain(): void
    {
        $admin = $this->admin();
        $adminLain = User::factory()->admin()->create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@example.com',
            'password' => 'rahasia123',
        ]);

        $this->actingAs($admin)
            ->patch('/admin/users/'.$admin->id.'/status')
            ->assertForbidden()
            ->assertSee('Akses ditolak. Akun admin tidak dapat dinonaktifkan dari halaman ini.');

        $this->actingAs($admin)
            ->patch('/admin/users/'.$adminLain->id.'/status')
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($adminLain->fresh()->is_active);
    }

    public function test_pendaftaran_publik_tidak_bisa_memilih_peran_admin(): void
    {
        $this->post('/register', [
            'name' => 'Calon Admin',
            'email' => 'calon@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'calon@example.com']);
    }

    public function test_perintah_admin_reate_membuat_akun_admin(): void
    {
        $status = Artisan::call('admin:create', [
            '--name' => 'Admin Demo',
            '--email' => 'demo@example.com',
            '--password' => 'rahasia123',
        ]);

        $this->assertSame(0, $status);
        $this->assertDatabaseHas('users', [
            'email' => 'demo@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $statusGagal = Artisan::call('admin:create', [
            '--name' => 'Admin Demo',
            '--email' => 'demo@example.com',
            '--password' => 'rahasia123',
        ]);

        $this->assertSame(1, $statusGagal);
    }

    public function test_api_tetap_mengembalikan_json_saat_ditolak(): void
    {
        $this->actingAs($this->buyer())
            ->getJson('/admin/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses ditolak. Halaman ini khusus admin.');
    }

    public function test_halaman_ditolak_menampilkan_tautan_kembali_per_peran(): void
    {
        $this->actingAs($this->buyer())
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee('Kembali ke halaman utama saya')
            ->assertSee(route('beranda'))
            ->assertDontSee(route('seller.dashboard'));

        $this->actingAs($this->seller())
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee(route('seller.dashboard'));
    }
}
