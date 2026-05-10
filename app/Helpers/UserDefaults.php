<?php

namespace App\Helpers;

use App\Models\Country;
use App\Models\Status;
use App\Models\User;

class UserDefaults
{
    /**
     * Create default statuses and categories for a newly registered user.
     */
    public static function seedForUser(User $user): void
    {
        // Default statuses
        $statuses = [
            ['name' => 'Backlog',     'color' => '#94a3b8', 'order' => 0, 'is_completed' => false],
            ['name' => 'To Do',       'color' => '#6366f1', 'order' => 1, 'is_completed' => false],
            ['name' => 'In Progress', 'color' => '#f59e0b', 'order' => 2, 'is_completed' => false],
            ['name' => 'Review',      'color' => '#8b5cf6', 'order' => 3, 'is_completed' => false],
            ['name' => 'Completed',   'color' => '#22c55e', 'order' => 4, 'is_completed' => true],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['name' => $status['name'], 'user_id' => $user->id],
                array_merge($status, ['user_id' => $user->id])
            );
        }

        // Default categories
        $categories = [
            ['name' => 'General', 'order' => 1],
        ];

        foreach ($categories as $category) {
            Country::firstOrCreate(
                ['name' => $category['name'], 'user_id' => $user->id],
                array_merge($category, ['user_id' => $user->id])
            );
        }
    }
}
