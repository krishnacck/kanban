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
        $countries = Country::orderBy('order')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function store(StoreCountryRequest $request)
    {
        Country::create($request->validated());
        return redirect()->route('countries.index')->with('success', 'Country created.');
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

        $country = Country::create([
            'name'  => $validated['name'],
            'order' => $validated['order'] ?? (Country::max('order') + 1),
        ]);

        return response()->json($country);
    }

    /**
     * Quick rename — available to all authenticated users (not admin-only).
     */
    public function rename(Request $request, Country $country)
    {
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
        $direction = $request->input('direction'); // 'up' or 'down'

        $countries = Country::orderBy('order')->get();
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
        $country->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($country->fresh());
        }
        return redirect()->route('countries.index')->with('success', 'Country updated.');
    }

    public function destroy(Country $country)
    {
        try {
            $country->deleteOrFail();
        } catch (ValidationException $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => $e->errors()], 422);
            }
            return redirect()->route('countries.index')->withErrors($e->errors());
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Country deleted.']);
        }
        return redirect()->route('countries.index')->with('success', 'Country deleted.');
    }
}
