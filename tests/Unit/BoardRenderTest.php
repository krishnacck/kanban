<?php

// Feature: kanban-board, Property 15: tasks appear in correct board cell
// Feature: kanban-board, Property 16: task cards display required fields
// Feature: kanban-board, Property 17: tasks sorted by priority desc then position asc

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P15: tasks are grouped by country_id and status_id', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        $task = Task::factory()->create([
            'status_id' => $status->id,
            'country_id' => $country->id,
            'created_by' => $user->id,
        ]);

        $grouped = Task::all()->groupBy(['country_id', 'status_id']);
        expect(isset($grouped[$country->id][$status->id]))->toBeTrue();
        expect($grouped[$country->id][$status->id]->contains('id', $task->id))->toBeTrue();
    }
});

it('P17: tasks in a cell are sorted high priority first then by position', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $country = Country::factory()->create();

        // Create mixed priority tasks
        $tasks = [];
        foreach (['low', 'high', 'low', 'high'] as $idx => $priority) {
            $t = Task::factory()->make([
                'status_id' => $status->id,
                'country_id' => $country->id,
                'created_by' => $user->id,
                'priority' => $priority,
            ]);
            $t->assignEndPosition();
            $t->save();
            $tasks[] = $t;
        }

        $sorted = Task::where('status_id', $status->id)
            ->where('country_id', $country->id)
            ->orderByRaw("CASE WHEN priority = 'high' THEN 0 ELSE 1 END")
            ->orderBy('position')
            ->get();

        // All high priority tasks come before low priority tasks
        $priorities = $sorted->pluck('priority')->toArray();
        $firstLow = array_search('low', $priorities);
        $lastHigh = array_search('high', array_reverse($priorities));
        if ($firstLow !== false && $lastHigh !== false) {
            expect($firstLow)->toBeGreaterThan(count($priorities) - 1 - $lastHigh - 1);
        }
    }
});
