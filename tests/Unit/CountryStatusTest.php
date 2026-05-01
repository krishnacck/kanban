<?php

// Feature: kanban-board, Property 9: cannot delete country/status with tasks

use App\Models\Country;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P9: cannot delete a country that has tasks', function () {
    for ($i = 0; $i < 100; $i++) {
        $country = Country::factory()->create();
        $status = Status::factory()->create();
        $user = User::factory()->create();

        Task::factory()->create([
            'country_id' => $country->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        expect(fn() => $country->deleteOrFail())->toThrow(ValidationException::class);
        expect(Country::find($country->id))->not->toBeNull();
    }
});

it('P9: cannot delete a status that has tasks', function () {
    for ($i = 0; $i < 100; $i++) {
        $country = Country::factory()->create();
        $status = Status::factory()->create();
        $user = User::factory()->create();

        Task::factory()->create([
            'country_id' => $country->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        expect(fn() => $status->deleteOrFail())->toThrow(ValidationException::class);
        expect(Status::find($status->id))->not->toBeNull();
    }
});
