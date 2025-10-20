<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone_number' => fake()->unique()->numerify('+1##########'),
        ];
    }
}
