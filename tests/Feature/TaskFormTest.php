<?php

// Feature: kanban-board, Property 22: assignee list contains all registered users

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P22: /users endpoint returns all registered users', function () {
    for ($i = 0; $i < 100; $i++) {
        User::query()->delete();
        $count = fake()->numberBetween(1, 10);
        User::factory()->count($count)->create();

        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->getJson('/users');

        $response->assertStatus(200);
        expect(count($response->json()))->toBe($count + 1);
    }
});
