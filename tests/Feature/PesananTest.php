<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pelengkap ProdukPesananApiTest: alur status pesanan, polling pesanan baru,
 * dan jawaban API untuk pengguna yang tidak berhak.
 */
class PesananTest extends TestCase
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

    private function produk(User $seller): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kue Lapis',
            'price' => 10000,
            'description' => 'Kue lapis multilayer',
            'audio_description' => 'Kue lapis dengan lapisan tipis dan manis',
        ]);
    }

    private function pesanan(User $buyer, Product $produk): Order
    {
        return Order::create([
            'buyer_id' => $buyer->id,
            'product_id' => $produk->id,
            'status' => Order::STATUS_BARU,
            'total_price' => $produk->price,
        ]);
    }

    public function test_penjual_boleh_menaikkan_status_pesanan(): void
    {
        $seller = $this->seller();
        $order = $this->pesanan($this->buyer(), $this->produk($seller));

        foreach ([Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM, Order::STATUS_SELESAI] as $status) {
            $this->actingAs($seller)
                ->patch('/seller/orders/'.$order->id.'/status', ['status' => $status])
                ->assertRedirect()
                ->assertSessionHasNoErrors();

            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_status_tidak_bisa_dikembalikan_ke_langkah_sebelumnya(): void
    {
        $seller = $this->seller();
        $order = $this->pesanan($this->buyer(), $this->produk($seller));

        $this->actingAs($seller)
            ->patch('/seller/orders/'.$order->id.'/status', ['status' => Order::STATUS_DIKIRIM])
            ->assertRedirect();

        $this->actingAs($seller)
            ->patch('/seller/orders/'.$order->id.'/status', ['status' => Order::STATUS_DIPROSES])
            ->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_DIKIRIM, $order->fresh()->status);
    }

    public function test_status_di_luar_daftar_ditolak(): void
    {
        $seller = $this->seller();
        $order = $this->pesanan($this->buyer(), $this->produk($seller));

        $this->actingAs($seller)
            ->patch('/seller/orders/'.$order->id.'/status', ['status' => 'dibatalkan'])
            ->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_BARU, $order->fresh()->status);
    }

    public function test_penjual_lain_tidak_bisa_mengubah_status_pesanan(): void
    {
        $order = $this->pesanan($this->buyer(), $this->produk($this->seller()));

        $this->actingAs($this->sellerLain())
            ->patch('/seller/orders/'.$order->id.'/status', ['status' => Order::STATUS_DIPROSES])
            ->assertForbidden();

        $this->assertSame(Order::STATUS_BARU, $order->fresh()->status);
    }

    public function test_api_pesanan_baru_terurut_id_naik(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $produk = $this->produk($seller);

        $order1 = $this->pesanan($buyer, $produk);
        $order2 = $this->pesanan($buyer, $produk);
        $order3 = $this->pesanan($buyer, $produk);

        $response = $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id=0');

        $response->assertOk()
            ->assertJsonPath('latest_id', $order3->id)
            ->assertJsonPath('orders.0.id', $order1->id)
            ->assertJsonPath('orders.1.id', $order2->id)
            ->assertJsonPath('orders.2.id', $order3->id);
    }

    public function test_polling_hanya_menampilkan_pesanan_baru(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $produk = $this->produk($seller);

        $orderLama = $this->pesanan($buyer, $produk);

        $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id=0')
            ->assertOk()
            ->assertJsonPath('latest_id', $orderLama->id);

        $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id='.$orderLama->id)
            ->assertOk()
            ->assertJsonPath('orders', [])
            ->assertJsonPath('latest_id', $orderLama->id);

        $orderBaru = $this->pesanan($buyer, $produk);

        $this->actingAs($seller)->getJson('/api/seller/orders/new?after_id='.$orderLama->id)
            ->assertOk()
            ->assertJsonPath('latest_id', $orderBaru->id)
            ->assertJsonPath('orders.0.id', $orderBaru->id)
            ->assertJsonPath('orders.1', null);
    }

    public function test_api_untuk_tamu_mengembalikan_json_bukan_halaman_masuk(): void
    {
        $this->getJson('/api/products')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');

        $this->getJson('/api/seller/orders/new')->assertUnauthorized();
    }

    public function test_api_pembeli_menolak_endpoint_penjual(): void
    {
        $this->actingAs($this->buyer())->getJson('/api/seller/orders/new?after_id=0')
            ->assertForbidden();
    }
}
