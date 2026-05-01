<?php

// Feature: kanban-board, Property 5: user-role permitted on own tasks
// Feature: kanban-board, Property 6: admin permitted on all resources
// Feature: kanban-board, Property 7: user-role receives 403 on admin-only routes
// Feature: kanban-board, Property 8: unauthenticated requests redirected to login
// Feature: kanban-board, Property 13: user-role can only edit tasks they own
// Feature: kanban-board, Property 23: unauthenticated API requests return 401

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P7: user-role gets 403 on admin-only routes', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/countries')->assertStatus(403);
        $this->actingAs($user)->get('/statuses')->assertStatus(403);
    }
});

it('P8: unauthenticated requests to protected routes redirect to login', function () {
    for ($i = 0; $i < 100; $i++) {
        $this->get('/board')->assertRedirect('/login');
    }
});

it('P23: unauthenticated API requests return 401', function () {
    for ($i = 0; $i < 100; $i++) {
        $this->getJson('/tasks')->assertStatus(401);
        $this->postJson('/tasks', [])->assertStatus(401);
    }
});

it('P13: user-role gets 403 when editing tasks they do not own', function () {
    for ($i = 0; $i < 100; $i++) {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $task = Task::factory()->create([
            'created_by' => $owner->id,
            'assigned_to' => null,
            'status_id' => $status->id,
            'country_id' => $country->id,
        ]);

        $response = $this->actingAs($other)->putJson("/tasks/{$task->id}", [
            'title' => 'Hacked title',
        ]);

        $response->assertStatus(403);
        expect($task->fresh()->title)->not->toBe('Hacked title');
    }
});

it('P6: admin can edit any task', function () {
    for ($i = 0; $i < 100; $i++) {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create(['role' => 'user']);
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $task = Task::factory()->create([
            'created_by' => $owner->id,
            'status_id' => $status->id,
            'country_id' => $country->id,
        ]);

        $newTitle = fake()->sentence(3);
        $response = $this->actingAs($admin)->putJson("/tasks/{$task->id}", [
            'title' => $newTitle,
        ]);

        $response->assertStatus(200);
        expect($task->fresh()->title)->toBe($newTitle);
    }
});
