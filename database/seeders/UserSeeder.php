<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Johnson',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Smith',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
    }
}
