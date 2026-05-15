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
        $query = Task::with(['status', 'country', 'assignee'])
            ->where('user_id', auth()->id());

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
        $data['user_id'] = auth()->id();
        $data['priority'] = $data['priority'] ?? 'low';
        $data['start_date'] = $data['start_date'] ?? now()->toDateString();

        $task = new Task($data);
        $task->assignEndPosition();
        $task->save();

        return response()->json($task->load(['status', 'country', 'assignee']), 201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorizeOwnership($task);

        $task->update($request->validated());

        return response()->json($task->load(['status', 'country', 'assignee']));
    }

    public function destroy(Task $task)
    {
        $this->authorizeOwnership($task);

        $statusId  = $task->status_id;
        $countryId = $task->country_id;

        $task->delete();

        Task::reindexCell($statusId, $countryId);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function complete(Task $task)
    {
        $this->authorizeOwnership($task);

        $completedStatus = \App\Models\Status::where('user_id', auth()->id())
            ->where('is_completed', true)
            ->orderBy('order')
            ->first();

        if (!$completedStatus) {
            return response()->json(['message' => 'No completed status configured.'], 422);
        }

        // Toggle: if already in a completed status, move back to first non-completed status
        $currentStatus = $task->status;
        if ($currentStatus && $currentStatus->is_completed) {
            $firstStatus = \App\Models\Status::where('user_id', auth()->id())
                ->where('is_completed', false)
                ->orderBy('order')
                ->first();
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

    private function authorizeOwnership(Task $task): void
    {
        if ($task->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not own this task.');
        }
    }
}
