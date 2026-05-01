<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    public function definition(): array
    {
        static $order = 0;
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->hexColor(),
            'order' => ++$order,
        ];
    }
}
