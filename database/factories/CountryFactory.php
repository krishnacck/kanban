<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        static $order = 0;
        return [
            'name' => fake()->unique()->country(),
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'order' => ++$order,
        ];
    }
}
