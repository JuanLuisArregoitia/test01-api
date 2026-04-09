<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Demo data
        $suppliers = Supplier::factory(5)->create();
        $products = Product::factory(15)->create();
        $clients = Client::factory(10)->create();

        // Relate some products to suppliers
        foreach ($products as $product) {
            $product->suppliers()->attach(
                $suppliers->random(rand(1, 2))->pluck('id')
            );
        }

        // Create orders with details
        $clients->each(function (Client $client) use ($products): void {
            $orders = Order::factory(rand(1, 3))->create(['client_id' => $client->id]);

            $orders->each(function (Order $order) use ($products): void {
                $selectedProducts = $products->random(rand(1, 4));

                foreach ($selectedProducts as $product) {
                    OrderDetail::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                    ]);
                }
            });
        });
    }
}
