<?php

namespace Database\Seeders;

use App\Models\schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        schedule::create([
            'plane_name' => 'Garuda Indonesia',
            'origin' => 'Jakarta',
            'destination' => 'Bali',
            'departure_time' => '2024-05-01 08:00:00',
            'price' => 1500000,
            'stock' => 100,
        ]);

        schedule::create([
            'plane_name' => 'Lion Air',
            'origin' => 'Jakarta',
            'destination' => 'Surabaya',
            'departure_time' => '2024-05-02 10:00:00',
            'price' => 800000,
            'stock' => 50,
        ]);

        schedule::create([
            'plane_name' => 'AirAsia',
            'origin' => 'Jakarta',
            'destination' => 'Medan',
            'departure_time' => '2024-05-03 12:00:00',
            'price' => 1200000,
            'stock' => 75,
        ]);

            schedule::create([
                'plane_name' => 'Citilink',
                'origin' => 'Jakarta',
                'destination' => 'Yogyakarta',
                'departure_time' => '2024-05-04 14:00:00',
                'price' => 900000,
                'stock' => 60,
            ]);
    }
}
