<?php

namespace Database\Seeders;

use App\Models\Vehicle_type;
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
          'vehicle_coefficient' => 0.8,
          'avg_fuel_consumption' => 5,
          'base_fare'=> 3000,
          'min_weight' => 0,
          'max_weight' => 30,       
          'min_length' => 0,
          'max_length' => 60,       
          'min_width' => 0,
          'max_width' => 50,        
          'min_height' => 0,
          'max_height' => 50,
        ]);
        Vehicle_type::query()->create([
          'type' => 'سيارة',
          'description' => 'توصيل صناديق وعلب متوسطة وكبيرة الحجم مثل حقائب السفر والأجهزة الكهربائية وغيرها',
          'vehicle_coefficient' => 1,
          'avg_fuel_consumption' => 10,
          'base_fare'=> 5000,
          'min_weight' => 20,
          'max_weight' => 200,   
          'min_length' => 30,
          'max_length' => 180,       
          'min_width' => 30,
          'max_width' => 120,        
          'min_height' => 30,
          'max_height' => 120,
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة صغيرة',
          'description' => 'نقل مواد بناء وأثاث منزلي أو مكتبي.. تتحمل حتى وزن 900 كيلو غرام وطول الصندوق 160 سم وعرض 140 سم',
          'vehicle_coefficient' => 1.2,
          'avg_fuel_consumption' => 15,
          'base_fare'=> 10000,
          'min_weight' => 150,
          'max_weight' => 1000,      
          'min_length' => 100,
          'max_length' => 220,
          'min_width' => 80,
          'max_width' => 160,
          'min_height' => 80,
          'max_height' => 200,
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة متوسطة',
          'description' => 'مناسبة لنقل البضائع الكبيرة أو الثقيلة.. تتحمل حمولة تصل إلى 2000 كيلو غرام وطول الصندوق 2.8 متر وعرض 2 متر',
          'vehicle_coefficient' => 1.5,
          'avg_fuel_consumption' => 18,
          'base_fare'=> 12000,
          'min_weight' => 800,
          'max_weight' => 3000,
          'min_length' => 200,
          'max_length' => 350,
          'min_width' => 150,
          'max_width' => 220,
          'min_height' => 150,
          'max_height' => 250,
        ]);
        Vehicle_type::query()->create([
          'type' => 'شاحنة كبيرة',
          'description' => 'نقل تجهيزات معامل وشركات أو أثاث منزلي كامل.. حمولة تصل إلى 4 طن وطول الصندوق 4 متر وعرض 2.2 متر',
          'vehicle_coefficient' => 1.8,
          'avg_fuel_consumption' => 22,
          'base_fare'=> 15000,
          'min_weight' => 2500,
          'max_weight' => 8000,
          'min_length' => 300,
          'max_length' => 600,
          'min_width' => 200,
          'max_width' => 260,
          'min_height' => 200,
          'max_height' => 300,
        ]);

    }
}
