<?php

namespace Database\Seeders;

use App\Helpers\UserDefaults;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        // Seed default statuses for all users who don't have any yet
        $usersWithoutStatuses = User::whereDoesntHave('statuses')->get();

        foreach ($usersWithoutStatuses as $user) {
            UserDefaults::seedForUser($user);
        }

        // If no users exist yet (fresh install), output a note
        if (User::count() === 0) {
            $this->command?->info('No users found. Default statuses will be created when users register.');
        } else {
            $this->command?->info("Seeded defaults for {$usersWithoutStatuses->count()} user(s).");
        }
    }
}
