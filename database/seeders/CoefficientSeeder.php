<?php

namespace Database\Seeders;

use App\Models\Coefficient;
use Illuminate\Database\Seeder;

class CoefficientSeeder extends Seeder
{

    public function run(): void
    {
        Coefficient::insert([
            [
                'name' => 'base_rate',
                'type' => 'pricing',
                'value' => 1000,
            ],
            [
                'name' => 'بنزين 90',
                'type' => 'fuel_price',
                'value' => 9900,
            ],
            [
                'name' => 'بنزين 95',
                'type' => 'fuel_price',
                'value' => 10600,
            ],
            [
                'name' => 'مازوت',
                'type' => 'fuel_price',
                'value' => 8800,
            ],
            [
                'name' => 'كهرباء',
                'type' => 'fuel_price',
                'value' => 3000,
            ],
            [
                'name' => 'insurance',
                'type' => 'insurance',
                'value' => 0.3,
            ],
        ]);
    }
}
