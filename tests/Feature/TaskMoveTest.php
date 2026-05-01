<?php

// Feature: kanban-board, Property 18: valid move re-indexes both cells
// Feature: kanban-board, Property 19: move with invalid status/country returns 422

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P18: valid move updates task and re-indexes both cells', function () {
    for ($i = 0; $i < 100; $i++) {
        // Reset faker unique state to avoid overflow after many iterations
        fake()->unique(true);

        $user = User::factory()->create(['role' => 'user']);
        $srcStatus = Status::factory()->create();
        $dstStatus = Status::factory()->create();
        $country = Country::factory()->create();

        // Create 3 tasks in source cell
        $tasks = [];
        for ($j = 0; $j < 3; $j++) {
            $t = Task::factory()->make([
                'status_id' => $srcStatus->id,
                'country_id' => $country->id,
                'created_by' => $user->id,
            ]);
            $t->assignEndPosition();
            $t->save();
            $tasks[] = $t;
        }

        $taskToMove = $tasks[1];

        $response = $this->actingAs($user)->postJson("/tasks/{$taskToMove->id}/move", [
            'status_id' => $dstStatus->id,
            'country_id' => $country->id,
            'position' => 0,
        ]);

        $response->assertStatus(200);

        // Source cell should have contiguous positions
        $srcPositions = Task::where('status_id', $srcStatus->id)
            ->where('country_id', $country->id)
            ->orderBy('position')
            ->pluck('position')
            ->toArray();
        expect($srcPositions)->toBe(range(0, count($srcPositions) - 1));

        // Target cell should have contiguous positions
        $dstPositions = Task::where('status_id', $dstStatus->id)
            ->where('country_id', $country->id)
            ->orderBy('position')
            ->pluck('position')
            ->toArray();
        expect($dstPositions)->toBe(range(0, count($dstPositions) - 1));
    }
});

it('P19: move with non-existent status_id returns 422', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create(['role' => 'user']);
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $task = Task::factory()->create([
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);

        $nonExistentId = 99999 + $i;

        $response = $this->actingAs($user)->postJson("/tasks/{$task->id}/move", [
            'status_id' => $nonExistentId,
            'country_id' => $country->id,
            'position' => 0,
        ]);

        $response->assertStatus(422);
        expect($task->fresh()->status_id)->toBe($status->id);
    }
});
