<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            StatusSeeder::class,   // Seeds defaults for users without statuses
            CountrySeeder::class,
            TaskSeeder::class,
        ]);
    }
}
