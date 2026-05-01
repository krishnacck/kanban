<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardsController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('tasks')
            ->orderByDesc('created_at')
            ->get();

        return view('boards.index', compact('boards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $board = Board::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return redirect("/board/{$board->id}");
    }

    public function destroy(Board $board)
    {
        $board->delete();
        return redirect('/boards')->with('success', 'Board deleted.');
    }
}
