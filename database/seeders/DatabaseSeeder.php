<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Data demo untuk seluruh mesin SuaraNiaga.
 *
 * Produk diambil dari config/suaranniaga.php supaya katalog, halaman detail,
 * dan pencarian suara memakai data yang sama. Id produk mengikuti id config
 * agar tautan /produk/{id} tetap konsisten.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const SELLER_EMAIL = 'seller@suaraniaga.test';

    public const BUYER_EMAIL = 'buyer@suaraniaga.test';

    public const ADMIN_EMAIL = 'admin@suaraniaga.test';

    public const PASSWORD = 'password123';

    public function run(): void
    {
        $seller = User::firstOrCreate(
            ['email' => self::SELLER_EMAIL],
            ['name' => 'Toko Necessitas', 'password' => self::PASSWORD, 'role' => User::ROLE_SELLER]
        );

        $buyer = User::firstOrCreate(
            ['email' => self::BUYER_EMAIL],
            ['name' => 'Budi Pembeli', 'password' => self::PASSWORD, 'role' => User::ROLE_BUYER]
        );

        User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            ['name' => 'Admin SuaraNiaga', 'password' => self::PASSWORD, 'role' => User::ROLE_ADMIN]
        );

        $produk = config('suaranniaga.produk', []);

        foreach ($produk as $data) {
            Product::updateOrCreate(
                ['id' => $data['id']],
                [
                    'seller_id' => $seller->id,
                    'name' => $data['nama'],
                    'price' => $data['harga'],
                    'description' => $data['deskripsi'],
                    'audio_description' => $data['audio'],
                ]
            );
        }

        if ($buyer->orders()->count() === 0) {
            Order::factory()->count(2)->create(['buyer_id' => $buyer->id]);
        }
    }
}
