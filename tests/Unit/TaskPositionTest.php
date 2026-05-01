<?php

// Feature: kanban-board, Property 11: task creation sets created_by and end position
// Feature: kanban-board, Property 14: task deletion re-indexes positions

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P11: task creation sets created_by and assigns end position', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $existingCount = fake()->numberBetween(0, 5);
        for ($j = 0; $j < $existingCount; $j++) {
            $t = Task::factory()->make([
                'status_id' => $status->id,
                'country_id' => $country->id,
                'created_by' => $user->id,
            ]);
            $t->assignEndPosition();
            $t->save();
        }

        $task = Task::factory()->make([
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);
        $task->assignEndPosition();
        $task->save();

        expect($task->created_by)->toBe($user->id);
        expect($task->position)->toBe($existingCount);
    }
});

it('P14: task deletion re-indexes remaining positions contiguously', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $count = fake()->numberBetween(2, 6);
        $tasks = [];
        for ($j = 0; $j < $count; $j++) {
            $t = Task::factory()->make([
                'status_id' => $status->id,
                'country_id' => $country->id,
                'created_by' => $user->id,
            ]);
            $t->assignEndPosition();
            $t->save();
            $tasks[] = $t;
        }

        $deleteIndex = fake()->numberBetween(0, $count - 1);
        $tasks[$deleteIndex]->delete();
        Task::reindexCell($status->id, $country->id);

        $remaining = Task::where('status_id', $status->id)
            ->where('country_id', $country->id)
            ->orderBy('position')
            ->pluck('position')
            ->toArray();

        expect($remaining)->toBe(range(0, $count - 2));
    }
});
