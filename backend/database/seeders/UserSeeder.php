<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1 admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create 2 teachers
        User::create([
            'name' => 'Teacher One',
            'email' => 'teacher1@school.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        User::create([
            'name' => 'Teacher Two',
            'email' => 'teacher2@school.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);
    }
}
