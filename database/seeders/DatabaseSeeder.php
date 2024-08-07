<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ShieldSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        Customer::factory(50)->create();
        Product::factory(50)->create();
        StockAdjustment::factory(50)->create();

        $this->call([OrderSeeder::class]);

        \Laravel\Prompts\info('Seeding completed!');
    }
}
