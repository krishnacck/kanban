<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveTaskRequest;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskMoveController extends Controller
{
    public function move(MoveTaskRequest $request, Task $task)
    {
        if ($task->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $sourceStatusId  = $task->status_id;
        $sourceCountryId = $task->country_id;

        $targetStatusId  = (int) $request->status_id;
        $targetCountryId = (int) $request->country_id;
        $targetPosition  = (int) $request->position;

        DB::transaction(function () use ($task, $sourceStatusId, $sourceCountryId, $targetStatusId, $targetCountryId, $targetPosition) {
            // Remove from source cell
            $task->status_id = $targetStatusId;
            $task->country_id = $targetCountryId;
            $task->position = $targetPosition;
            $task->save();

            // Re-index source cell (if different from target)
            if ($sourceStatusId !== $targetStatusId || $sourceCountryId !== $targetCountryId) {
                Task::reindexCell($sourceStatusId, $sourceCountryId);
            }

            // Shift tasks in target cell to make room
            Task::where('status_id', $targetStatusId)
                ->where('country_id', $targetCountryId)
                ->where('id', '!=', $task->id)
                ->where('position', '>=', $targetPosition)
                ->increment('position');

            // Re-index target cell for clean sequence
            Task::reindexCell($targetStatusId, $targetCountryId);
        });

        return response()->json($task->fresh()->load(['status', 'country', 'assignee']));
    }
}
