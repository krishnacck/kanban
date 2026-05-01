<?php

// Feature: kanban-board, Property 12: invalid task inputs rejected without persisting

use App\Models\Country;
use App\Models\Status;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P12: missing required fields return 422 and do not persist task', function () {
    $user = User::factory()->create();
    $status = Status::factory()->create();
    $country = Country::factory()->create();

    for ($i = 0; $i < 100; $i++) {
        $countBefore = \App\Models\Task::count();

        // Missing title
        $response = $this->actingAs($user)->postJson('/tasks', [
            'status_id' => $status->id,
            'country_id' => $country->id,
        ]);
        $response->assertStatus(422);
        expect(\App\Models\Task::count())->toBe($countBefore);

        // Invalid priority
        $response = $this->actingAs($user)->postJson('/tasks', [
            'title' => fake()->sentence(3),
            'status_id' => $status->id,
            'country_id' => $country->id,
            'priority' => 'urgent', // invalid
        ]);
        $response->assertStatus(422);
        expect(\App\Models\Task::count())->toBe($countBefore);
    }
});
