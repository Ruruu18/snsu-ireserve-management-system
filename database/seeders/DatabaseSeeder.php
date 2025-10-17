<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin Account
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Student Account
        User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        // Seed equipment data
        $this->call([
            DepartmentSeeder::class,
            EquipmentSeeder::class,
        ]);
    }
}
