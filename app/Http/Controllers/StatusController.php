<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StatusController extends Controller
{
    public function index()
    {
        $statuses = Status::orderBy('order')->get();
        return view('admin.statuses.index', compact('statuses'));
    }

    public function store(StoreStatusRequest $request)
    {
        $status = Status::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json($status, 201);
        }

        return redirect()->route('statuses.index')->with('success', 'Status created.');
    }

    public function update(UpdateStatusRequest $request, Status $status)
    {
        $status->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($status->fresh());
        }
        return redirect()->route('statuses.index')->with('success', 'Status updated.');
    }

    public function destroy(Status $status)
    {
        try {
            $status->deleteOrFail();
        } catch (ValidationException $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Cannot delete a status that has tasks assigned to it.'], 422);
            }
            return redirect()->route('statuses.index')->withErrors($e->errors());
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('statuses.index')->with('success', 'Status deleted.');
    }

    public function move(Request $request, Status $status)
    {
        $direction = $request->input('direction', 'up');
        $statuses = Status::orderBy('order')->get();
        $index = $statuses->search(fn($s) => $s->id === $status->id);

        if ($direction === 'up' && $index > 0) {
            $swap = $statuses[$index - 1];
            $tmpOrder = $status->order;
            $status->update(['order' => $swap->order]);
            $swap->update(['order' => $tmpOrder]);
        } elseif ($direction === 'down' && $index < $statuses->count() - 1) {
            $swap = $statuses[$index + 1];
            $tmpOrder = $status->order;
            $status->update(['order' => $swap->order]);
            $swap->update(['order' => $tmpOrder]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back();
    }

    public function rename(Request $request, Status $status)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $status->update(['name' => $request->name]);

        if ($request->expectsJson()) {
            return response()->json($status->fresh());
        }
        return back()->with('success', 'Status renamed.');
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $maxOrder = Status::max('order') ?? 0;

        $status = Status::create([
            'name'  => $request->name,
            'color' => '#6366f1',
            'order' => $maxOrder + 1,
            'is_completed' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json($status, 201);
        }
        return back()->with('success', 'Status created.');
    }
}
