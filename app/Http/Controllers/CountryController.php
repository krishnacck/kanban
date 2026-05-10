<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::where('user_id', auth()->id())->orderBy('order')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function store(StoreCountryRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        Country::create($data);
        return redirect()->route('countries.index')->with('success', 'Category created.');
    }

    /**
     * Quick create — available to all authenticated users.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $userId = auth()->id();
        $maxOrder = Country::where('user_id', $userId)->max('order') ?? 0;

        $country = Country::create([
            'name'    => $validated['name'],
            'order'   => $validated['order'] ?? ($maxOrder + 1),
            'user_id' => $userId,
        ]);

        return response()->json($country);
    }

    /**
     * Search for similar category names across all users to avoid duplicates.
     */
    public function suggest(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Country::where('name', 'like', '%' . $query . '%')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(10)
            ->pluck('name');

        return response()->json($suggestions);
    }

    /**
     * Quick rename — available to all authenticated users.
     */
    public function rename(Request $request, Country $country)
    {
        $this->authorizeOwnership($country);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $country->update($validated);

        return response()->json($country->fresh());
    }

    /**
     * Move a country up or down by swapping order values with its neighbour.
     */
    public function move(Request $request, Country $country)
    {
        $this->authorizeOwnership($country);

        $direction = $request->input('direction'); // 'up' or 'down'

        $countries = Country::where('user_id', auth()->id())->orderBy('order')->get();
        $index     = $countries->search(fn($c) => $c->id === $country->id);

        if ($direction === 'up' && $index > 0) {
            $neighbour = $countries[$index - 1];
        } elseif ($direction === 'down' && $index < $countries->count() - 1) {
            $neighbour = $countries[$index + 1];
        } else {
            return response()->json(['message' => 'Already at boundary.'], 422);
        }

        // Swap order values
        [$country->order, $neighbour->order] = [$neighbour->order, $country->order];
        $country->save();
        $neighbour->save();

        return response()->json(['message' => 'Moved.']);
    }

    public function update(UpdateCountryRequest $request, Country $country)
    {
        $this->authorizeOwnership($country);

        $country->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($country->fresh());
        }
        return redirect()->route('countries.index')->with('success', 'Category updated.');
    }

    public function destroy(Country $country)
    {
        $this->authorizeOwnership($country);

        try {
            $country->deleteOrFail();
        } catch (ValidationException $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => $e->errors()], 422);
            }
            return redirect()->route('countries.index')->withErrors($e->errors());
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Category deleted.']);
        }
        return redirect()->route('countries.index')->with('success', 'Category deleted.');
    }

    private function authorizeOwnership(Country $country): void
    {
        if ($country->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not own this category.');
        }
    }
}
