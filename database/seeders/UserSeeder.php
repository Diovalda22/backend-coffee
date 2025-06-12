<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin Satu',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'role' => 2, // Role 2 = Admin
        ]);

        // Customer user
        User::create([
            'name' => 'Customer Satu',
            'email' => 'asd@asd.asd',
            'password' => Hash::make('asdasd'),
            'role' => 1, // Role 1 = Customer
        ]);
    }
}
