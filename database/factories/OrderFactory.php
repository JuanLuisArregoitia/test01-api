<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => strtoupper($this->faker->bothify('ORD-####-??')),
            'status_id' => $this->faker->numberBetween(1, 3),
            'client_id' => Client::factory(),
        ];
    }
}
