<?php

// Feature: kanban-board, Property 10: countries and statuses sorted ascending by order

use App\Models\Country;
use App\Models\Status;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P10: countries are returned sorted ascending by order', function () {
    for ($i = 0; $i < 100; $i++) {
        Country::query()->delete();
        // Reset faker unique state to avoid overflow after many iterations
        fake()->unique(true);
        $orders = collect(range(1, 5))->shuffle()->values();
        foreach ($orders as $order) {
            Country::factory()->create(['order' => $order]);
        }

        $fetched = Country::orderBy('order')->pluck('order')->toArray();
        $sorted = $fetched;
        sort($sorted);
        expect($fetched)->toBe($sorted);
    }
});

it('P10: statuses are returned sorted ascending by order', function () {
    for ($i = 0; $i < 100; $i++) {
        Status::query()->delete();
        // Reset faker unique state to avoid overflow after many iterations
        fake()->unique(true);
        $orders = collect(range(1, 5))->shuffle()->values();
        foreach ($orders as $order) {
            Status::factory()->create(['order' => $order]);
        }

        $fetched = Status::orderBy('order')->pluck('order')->toArray();
        $sorted = $fetched;
        sort($sorted);
        expect($fetched)->toBe($sorted);
    }
});
