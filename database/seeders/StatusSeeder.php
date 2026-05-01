<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'To Do',      'color' => '#6366f1', 'order' => 1, 'is_completed' => false],
            ['name' => 'In Progress','color' => '#f59e0b', 'order' => 2, 'is_completed' => false],
            ['name' => 'Review',     'color' => '#8b5cf6', 'order' => 3, 'is_completed' => false],
            ['name' => 'Completed',  'color' => '#6b7280', 'order' => 4, 'is_completed' => true],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
