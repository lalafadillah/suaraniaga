<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        $quantity = fake()->numberBetween(1, 3);

        return [
            'buyer_id' => User::where('role', User::ROLE_BUYER)->value('id') ?? User::factory()->create(['role' => User::ROLE_BUYER])->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'status' => Order::STATUS_BARU,
            'total_price' => $product->price * $quantity,
        ];
    }
}
