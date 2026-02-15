<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name' => 'دمشق'],
            ['name' => 'ريف دمشق'],
            ['name' => 'حلب'],
            ['name' => 'اللاذقية'],
            ['name' => 'حماة'],
            ['name' => 'حمص'],
            ['name' => 'درعا'],
            ['name' => 'القنيطرة'],
            ['name' => 'الرقة'],
            ['name' => 'دير الزور'],
            ['name' => 'الحسكة'],
            ['name' => 'إدلب'],
            ['name' => 'السويداء'],
            ['name' => 'طرطوس'],
        ];

        foreach ($governorates as $gov) {
            Governorate::query()->create($gov);
        }
    }
}
