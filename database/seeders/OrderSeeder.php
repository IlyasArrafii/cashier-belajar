<?php

namespace Database\Seeders;

use App\Models\Order;
use Carbon\CarbonPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = now()->subDays(100);
        $endDate = now();
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $randomRecordsCount = rand(1, 4); // Atur range sesuai kebutuhan

            for ($i = 0; $i < $randomRecordsCount; $i++) {
                Order::factory()->create([
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
