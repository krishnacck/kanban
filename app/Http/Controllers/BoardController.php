<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        // Load all countries and statuses (board_id agnostic — supports both
        // legacy null board_id rows and future board-scoped rows)
        $countries = Country::orderBy('order')->get();
        $statuses  = Status::orderBy('order')->get();
        $users     = User::orderBy('name')->get();

        $query = Task::with(['assignee'])
            ->orderByRaw("CASE WHEN priority = 'high' THEN 0 WHEN priority = 'medium' THEN 1 ELSE 2 END")
            ->orderBy('position');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
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

        $tasks = $query->get()->groupBy(['country_id', 'status_id']);

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('board._grid', compact('countries', 'statuses', 'tasks'))->render();
        }

        // Task counts per country for sidebar
        $taskCounts = ['total' => 0];
        foreach ($countries as $country) {
            $count = isset($tasks[$country->id])
                ? collect($tasks[$country->id])->flatten()->count()
                : 0;
            $taskCounts[$country->id] = $count;
            $taskCounts['total'] += $count;
        }

        return view('board.index', compact('countries', 'statuses', 'tasks', 'users', 'taskCounts'));
    }
}
