<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
          'email' => 'application.shipments@gmail.com',
          'password' => '123sS123##',
          'phone_number' => '0937523553',
          'first_name' => 'admin',
          'last_name' => 'admin',
          'location' => 'Damascus',
          'user_number' => '33221100',
          'email_verified_at' => now(),
          'role_id' => '1',
        ]);

    }
}
