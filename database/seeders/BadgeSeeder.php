<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Badge::query()->create([
          'level' => '0',
          'name' => 'مُعتمد',
          'text' => 'مُعتمد',
          'continuous_successful_shipments_condition' => '0',
          'successful_shipments_percentage_condition' => '0',
          'continuous_failed_shipments_condition' => '0',
        ]);
        Badge::query()->create([
          'level' => '1',
          'name' => 'مُنتظم',
          'text' => 'مُنتظم',
          'continuous_successful_shipments_condition' => '15',
          'successful_shipments_percentage_condition' => '0.7',
          'continuous_failed_shipments_condition' => '2',
        ]);
        Badge::query()->create([
          'level' => '2',
          'name' => 'خبير',
          'text' => 'خبير',
          'continuous_successful_shipments_condition' => '30',
          'successful_shipments_percentage_condition' => '0.8',
          'continuous_failed_shipments_condition' => '3',
        ]);
        Badge::query()->create([
          'level' => '3',
          'name' => 'مضمون',
          'text' => 'مضمون',
          'continuous_successful_shipments_condition' => '60',
          'successful_shipments_percentage_condition' => '0.9',
          'continuous_failed_shipments_condition' => '3',
        ]);

    }
}
