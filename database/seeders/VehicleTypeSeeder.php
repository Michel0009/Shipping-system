<?php

namespace Database\Seeders;

use App\Models\Vehicle_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vehicle_type::query()->create([
          'type' => 'دراجة نارية',
          'description' => 'خدمة توصيل فورية للأغراض صغيرة الحجم.. مثل قطع السيارات واكسسورات وصناديق صغيرة بوزن لا يتجاوز 30 كيلو غرام',
          'vehicle_coefficient' => '0.8',
          'avg_fuel_consumption' => '6',
        ]);
        Vehicle_type::query()->create([
          'type' => 'سيارة',
          'description' => 'توصيل صناديق وعلب متوسطة وكبيرة الحجم مثل حقائب السفر والأجهزة الكهربائية وغيرها',
          'vehicle_coefficient' => '1',
          'avg_fuel_consumption' => '12',
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة صغيرة',
          'description' => 'نقل مواد بناء وأثاث منزلي أو مكتبي.. تتحمل حتى وزن 900 كيلو غرام وطول الصندوق 160 سم وعرض 140 سم',
          'vehicle_coefficient' => '1.2',
          'avg_fuel_consumption' => '15',
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة متوسطة',
          'description' => 'مناسبة لنقل البضائع الكبيرة أو الثقيلة.. تتحمل حمولة تصل إلى 2000 كيلو غرام وطول الصندوق 2.8 متر وعرض 2 متر',
          'vehicle_coefficient' => '1.4',
          'avg_fuel_consumption' => '18',
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة كبيرة',
          'description' => 'نقل تجهيزات معامل وشركات أو أثاث منزلي كامل.. حمولة تصل إلى 4 طن وطول الصندوق 4 متر وعرض 2.2 متر',
          'vehicle_coefficient' => '1.6',
          'avg_fuel_consumption' => '22',
        ]);

    }
}
