<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(['high', 'low']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'position' => 0,
            'status_id' => Status::factory(),
            'country_id' => Country::factory(),
            'created_by' => User::factory(),
            'assigned_to' => null,
        ];
    }
}
