<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Status;
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
        Status::create($request->validated());
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
            return redirect()->route('statuses.index')->withErrors($e->errors());
        }
        return redirect()->route('statuses.index')->with('success', 'Status deleted.');
    }
}
