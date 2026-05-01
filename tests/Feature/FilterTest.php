<?php

// Feature: kanban-board, Property 20: multi-filter returns only matching tasks
// Feature: kanban-board, Property 21: clearing filters restores full set

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P20: filter by priority returns only matching tasks', function () {
    $user = User::factory()->create();
    $status = Status::factory()->create();
    $country = Country::factory()->create();

    for ($i = 0; $i < 100; $i++) {
        // Clean up tasks from previous iteration
        Task::query()->delete();

        Task::factory()->count(3)->create([
            'priority' => 'high',
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);
        Task::factory()->count(2)->create([
            'priority' => 'low',
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/tasks?priority=high');
        $response->assertStatus(200);
        $tasks = $response->json();
        foreach ($tasks as $task) {
            expect($task['priority'])->toBe('high');
        }
        expect(count($tasks))->toBe(3);
    }
});

it('P21: clearing filters returns all tasks', function () {
    $user = User::factory()->create();
    $status = Status::factory()->create();
    $country = Country::factory()->create();

    for ($i = 0; $i < 100; $i++) {
        // Clean up tasks from previous iteration
        Task::query()->delete();

        $total = fake()->numberBetween(2, 8);
        Task::factory()->count($total)->create([
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/tasks');
        $response->assertStatus(200);
        expect(count($response->json()))->toBe($total);
    }
});
