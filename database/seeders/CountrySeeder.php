<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'order' => 1],
            ['name' => 'United Kingdom', 'code' => 'GB', 'order' => 2],
            ['name' => 'Germany', 'code' => 'DE', 'order' => 3],
            ['name' => 'France', 'code' => 'FR', 'order' => 4],
        ];

        // Seed categories for all users who only have the default "General" category
        $users = User::all();

        foreach ($users as $user) {
            foreach ($countries as $country) {
                Country::firstOrCreate(
                    ['code' => $country['code'], 'user_id' => $user->id],
                    array_merge($country, ['user_id' => $user->id])
                );
            }
        }
    }
}
