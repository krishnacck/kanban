<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) return;

        $alice = User::where('email', 'alice@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();

        // Get user-scoped statuses and countries for admin
        $us = Country::where('code', 'US')->where('user_id', $admin->id)->first();
        $gb = Country::where('code', 'GB')->where('user_id', $admin->id)->first();
        $de = Country::where('code', 'DE')->where('user_id', $admin->id)->first();

        $todo = Status::where('name', 'To Do')->where('user_id', $admin->id)->first();
        $inProgress = Status::where('name', 'In Progress')->where('user_id', $admin->id)->first();
        $done = Status::where('name', 'Completed')->where('user_id', $admin->id)->first();

        if (!$us || !$todo || !$inProgress) return;

        $tasks = [
            [
                'title' => 'Set up CI/CD pipeline',
                'description' => 'Configure GitHub Actions for automated testing and deployment.',
                'priority' => 'high',
                'status_id' => $todo->id,
                'country_id' => $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => $alice?->id,
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Design landing page',
                'description' => 'Create wireframes and mockups for the new landing page.',
                'priority' => 'low',
                'status_id' => $inProgress->id,
                'country_id' => $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => $alice?->id,
                'due_date' => now()->addDays(14)->toDateString(),
            ],
            [
                'title' => 'GDPR compliance review',
                'description' => 'Review data handling practices for EU compliance.',
                'priority' => 'high',
                'status_id' => $todo->id,
                'country_id' => $gb?->id ?? $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => $bob?->id,
                'due_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'title' => 'Localize UI for German market',
                'description' => 'Translate all UI strings to German.',
                'priority' => 'low',
                'status_id' => $inProgress->id,
                'country_id' => $de?->id ?? $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => $bob?->id,
                'due_date' => null,
            ],
            [
                'title' => 'Performance audit',
                'description' => 'Run Lighthouse audit and fix issues.',
                'priority' => 'high',
                'status_id' => $done?->id ?? $todo->id,
                'country_id' => $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => null,
                'due_date' => now()->subDays(2)->toDateString(),
            ],
            [
                'title' => 'Write API documentation',
                'description' => 'Document all REST endpoints using OpenAPI spec.',
                'priority' => 'low',
                'status_id' => $todo->id,
                'country_id' => $gb?->id ?? $us->id,
                'created_by' => $admin->id,
                'user_id' => $admin->id,
                'assigned_to' => $alice?->id,
                'due_date' => now()->addDays(21)->toDateString(),
            ],
        ];

        foreach ($tasks as $taskData) {
            if (!Task::where('title', $taskData['title'])->where('user_id', $admin->id)->exists()) {
                $task = new Task($taskData);
                $task->assignEndPosition();
                $task->save();
            }
        }
    }
}
