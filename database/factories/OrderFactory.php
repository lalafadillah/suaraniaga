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

        return [
            'buyer_id' => User::where('role', User::ROLE_BUYER)->value('id') ?? User::factory()->create(['role' => User::ROLE_BUYER])->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_BARU,
            'total_price' => $product->price,
        ];
    }
}
