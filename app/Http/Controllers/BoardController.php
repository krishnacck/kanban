<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $countries = Country::where('user_id', $user->id)->orderBy('order')->get();
        $statuses  = Status::where('user_id', $user->id)->orderBy('order')->get();
        $users     = User::orderBy('name')->get();

        $groupRow = $request->input('group_row', 'category');
        $groupCol = $request->input('group_col', 'status');

        $query = Task::with(['assignee', 'status', 'country'])
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN priority = 'high' THEN 0 WHEN priority = 'medium' THEN 1 ELSE 2 END")
            ->orderBy('position');

        if ($request->filled('country_id'))  $query->where('country_id',  $request->country_id);
        if ($request->filled('status_id'))   $query->where('status_id',   $request->status_id);
        if ($request->filled('priority'))    $query->where('priority',    $request->priority);
        if ($request->filled('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        if ($request->filled('search'))      $query->where('title', 'like', '%'.$request->search.'%');

        $allTasks = $query->get();

        // Build row groups
        [$rowGroups, $rowLabel] = $this->buildGroups($allTasks, $groupRow, $countries, $statuses, $users);

        // Build column groups
        [$colGroups, $colLabel] = $this->buildGroups($allTasks, $groupCol, $countries, $statuses, $users);

        // Build task matrix: rowKey → colKey → tasks[]
        $matrix = [];
        foreach ($rowGroups as $rKey => $rMeta) {
            $matrix[$rKey] = [];
            foreach ($colGroups as $cKey => $cMeta) {
                $matrix[$rKey][$cKey] = $allTasks->filter(function ($task) use ($groupRow, $rKey, $groupCol, $cKey) {
                    return $this->taskMatchesGroup($task, $groupRow, $rKey)
                        && $this->taskMatchesGroup($task, $groupCol, $cKey);
                })->values();
            }
        }

        $isAjax = $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        $viewData = compact('rowGroups', 'colGroups', 'matrix', 'groupRow', 'groupCol', 'rowLabel', 'colLabel', 'statuses', 'countries');

        if ($isAjax) {
            return view('board._grid_dynamic', $viewData)->render();
        }

        // Task counts per country for sidebar (always category-based)
        $taskCounts = ['total' => $allTasks->count()];
        foreach ($countries as $country) {
            $taskCounts[$country->id] = $allTasks->where('country_id', $country->id)->count();
        }

        return view('board.index', array_merge($viewData, compact('users', 'taskCounts')));
    }

    private function buildGroups(Collection $tasks, string $groupBy, $countries, $statuses, $users): array
    {
        switch ($groupBy) {
            case 'category':
                $groups = [];
                foreach ($countries as $c) {
                    $groups[$c->id] = [
                        'label'        => $c->name,
                        'icon'         => $c->code && strlen($c->code) === 2 ? countryFlag($c->code) : '🏷️',
                        'color'        => '#6750A4',
                        'is_completed' => false,
                        'meta'         => $c,
                    ];
                }
                return [$groups, 'Category'];

            case 'status':
                $groups = [];
                foreach ($statuses as $s) {
                    $groups[$s->id] = [
                        'label'        => $s->name,
                        'icon'         => null,
                        'color'        => $s->color,
                        'is_completed' => $s->is_completed,
                        'meta'         => $s,
                    ];
                }
                return [$groups, 'Status'];

            case 'priority':
                return [[
                    'high'   => ['label' => 'High',   'icon' => '🔴', 'color' => '#DC2626', 'is_completed' => false, 'meta' => null],
                    'medium' => ['label' => 'Medium', 'icon' => '🟡', 'color' => '#CA8A04', 'is_completed' => false, 'meta' => null],
                    'low'    => ['label' => 'Low',    'icon' => '🟢', 'color' => '#16A34A', 'is_completed' => false, 'meta' => null],
                ], 'Priority'];

            case 'assignee':
                $groups = ['unassigned' => ['label' => 'Unassigned', 'icon' => '👤', 'color' => '#79747E', 'is_completed' => false, 'meta' => null]];
                foreach ($users as $u) {
                    $groups[$u->id] = [
                        'label'        => $u->name,
                        'icon'         => $u->avatar ? null : strtoupper(substr($u->name, 0, 1)),
                        'avatar'       => $u->avatar,
                        'color'        => '#6750A4',
                        'is_completed' => false,
                        'meta'         => $u,
                    ];
                }
                return [$groups, 'Assignee'];

            case 'month':
                $months = $tasks->filter(fn($t) => $t->due_date)
                    ->map(fn($t) => $t->due_date->format('Y-m'))
                    ->unique()->sort()->values();
                $groups = [];
                foreach ($months as $m) {
                    $label = \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y');
                    $groups[$m] = ['label' => $label, 'icon' => '📅', 'color' => '#6750A4', 'is_completed' => false, 'meta' => null];
                }
                $groups['no_date'] = ['label' => 'No Due Date', 'icon' => '—', 'color' => '#79747E', 'is_completed' => false, 'meta' => null];
                return [$groups, 'Due Month'];

            case 'none':
                return [['all' => ['label' => 'All Tasks', 'icon' => '📋', 'color' => '#6750A4', 'is_completed' => false, 'meta' => null]], ''];

            default:
                return [['all' => ['label' => 'All', 'icon' => null, 'color' => '#6750A4', 'is_completed' => false, 'meta' => null]], ''];
        }
    }

    private function taskMatchesGroup(Task $task, string $groupBy, $key): bool
    {
        return match ($groupBy) {
            'category' => (string) $task->country_id === (string) $key,
            'status'   => (string) $task->status_id  === (string) $key,
            'priority' => $task->priority === $key,
            'assignee' => $key === 'unassigned' ? is_null($task->assigned_to) : (string) $task->assigned_to === (string) $key,
            'month'    => $key === 'no_date'
                ? is_null($task->due_date)
                : ($task->due_date && $task->due_date->format('Y-m') === $key),
            'none'     => true,
            default    => true,
        };
    }
}
