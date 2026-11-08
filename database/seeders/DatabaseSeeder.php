<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const SELLER_EMAIL = 'seller@suaraniaga.test';

    public const BUYER_EMAIL = 'buyer@suaraniaga.test';

    public const PASSWORD = 'password123';

    private array $products = [
        [
            'name' => 'Kue Lapis Legit',
            'price' => 10000,
            'description' => 'Kue lapis multilayer dengan santan premium, lembut dan tidak terlalu manis.',
            'audio_description' => 'Kue Lapis Legit. Harga sepuluh ribu rupiah. Lapisan tipis yang lembut, dibuat dengan santan premium.',
        ],
        [
            'name' => 'Kue Cubit Mentega',
            'price' => 15000,
            'description' => 'Kue cubit setengah matang dengan aroma butter yang kuat.',
            'audio_description' => 'Kue Cubit Mentega. Harga lima belas ribu rupiah. Kue setengah matang dengan aroma butter yang harum.',
        ],
        [
            'name' => 'Kerupuk Udang',
            'price' => 25000,
            'description' => 'Kerupuk udang renyah, digoreng ulang saat dikirim.',
            'audio_description' => 'Kerupuk Udang. Harga dua puluh lima ribu rupiah. Renyah dan gurih, digoreng ulang sebelum dikirim.',
        ],
        [
            'name' => 'Es Kriming Cokelat',
            'price' => 8000,
            'description' => 'Es kriming cokelat dengan potongan cokelat pekat.',
            'audio_description' => 'Es Kriming Cokelat. Harga delapan ribu rupiah. Berisi cokelat pekat dan ais kriming yang lembut.',
        ],
        [
            'name' => 'Baju Batik Tulis Madu',
            'price' => 120000,
            'description' => 'Kemeja batik tulis tangan dengan motif daun dan bahan katun.',
            'audio_description' => 'Baju Batik Tulis Madu. Harga seratus dua puluh ribu rupiah. Kemeja batik tulis tangan dengan motif daun.',
        ],
        [
            'name' => 'Sambal Roa Manado',
            'price' => 35000,
            'description' => 'Sambal roa khas Manado dengan ikan asap dan Singkong.',
            'audio_description' => 'Sambal Roa Manado. Harga tiga puluh lima ribu rupiah. Sambal khas Manado dengan ikan asap yang gurih.',
        ],
    ];

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

        foreach ($this->products as $data) {
            Product::updateOrCreate(
                ['seller_id' => $seller->id, 'name' => $data['name']],
                [
                    'price' => $data['price'],
                    'description' => $data['description'],
                    'audio_description' => $data['audio_description'],
                ]
            );
        }

        if ($buyer->orders()->count() === 0) {
            Order::factory()->count(2)->create(['buyer_id' => $buyer->id]);
        }
    }
}
