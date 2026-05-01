<?php

namespace Database\Seeders;

use App\Models\Country;
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

        foreach ($countries as $country) {
            Country::firstOrCreate(['code' => $country['code']], $country);
        }
    }
}
