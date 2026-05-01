<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['status', 'country', 'assignee']);

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->orderByRaw("CASE WHEN priority = 'high' THEN 0 ELSE 1 END")
            ->orderBy('position')
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['priority'] = $data['priority'] ?? 'low';

        $task = new Task($data);
        $task->assignEndPosition();
        $task->save();

        return response()->json($task->load(['status', 'country', 'assignee']), 201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $task->created_by !== $user->id && $task->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $task->update($request->validated());

        return response()->json($task->load(['status', 'country', 'assignee']));
    }

    public function destroy(Task $task)
    {
        $statusId  = $task->status_id;
        $countryId = $task->country_id;

        $task->delete();

        Task::reindexCell($statusId, $countryId);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function complete(Task $task)
    {
        $completedStatus = \App\Models\Status::where('is_completed', true)->orderBy('order')->first();

        if (!$completedStatus) {
            return response()->json(['message' => 'No completed status configured.'], 422);
        }

        // Toggle: if already in a completed status, move back to first non-completed status
        $currentStatus = $task->status;
        if ($currentStatus && $currentStatus->is_completed) {
            $firstStatus = \App\Models\Status::where('is_completed', false)->orderBy('order')->first();
            if ($firstStatus) {
                $task->status_id = $firstStatus->id;
                $task->assignEndPosition();
                $task->save();
                Task::reindexCell($completedStatus->id, $task->country_id);
            }
        } else {
            $oldStatusId = $task->status_id;
            $task->status_id = $completedStatus->id;
            $task->assignEndPosition();
            $task->save();
            Task::reindexCell($oldStatusId, $task->country_id);
        }

        return response()->json($task->fresh()->load(['status', 'country', 'assignee']));
    }
}
