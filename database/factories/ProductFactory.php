<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Kue Lapis',
            'Kue Cubit',
            'Kerupuk Udang',
            'Es Kriming',
            'Baju Batik',
            'Sambal Roa',
        ]);

        $price = fake()->numberBetween(5, 200) * 1000;

        return [
            'seller_id' => User::where('role', User::ROLE_SELLER)->value('id') ?? User::factory()->create(['role' => User::ROLE_SELLER])->id,
            'name' => $name,
            'price' => $price,
            'description' => $name.' segar, dibuat dengan bahan pilihan dan tanpa pengawet.',
            'audio_description' => $name.' dengan harga '.number_format($price, 0, ',', '.').' rupiah. Dibuat dengan bahan pilihan.',
            'image_url' => null,
        ];
    }
}
