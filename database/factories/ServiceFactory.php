<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $services = [
            ['name' => 'Haircut', 'duration' => 30, 'color' => '#FF6B6B'],
            ['name' => 'Hair Coloring', 'duration' => 120, 'color' => '#4ECDC4'],
            ['name' => 'Manicure', 'duration' => 45, 'color' => '#45B7D1'],
            ['name' => 'Pedicure', 'duration' => 60, 'color' => '#FFA07A'],
            ['name' => 'Massage Therapy', 'duration' => 60, 'color' => '#98D8C8'],
            ['name' => 'Facial Treatment', 'duration' => 90, 'color' => '#F7DC6F'],
            ['name' => 'Consultation', 'duration' => 30, 'color' => '#BB8FCE'],
            ['name' => 'Beard Trim', 'duration' => 20, 'color' => '#85C1E2'],
        ];

        $service = fake()->randomElement($services);

        return [
            'name' => $service['name'],
            'duration' => $service['duration'],
            'price' => fake()->randomFloat(2, 20, 150),
            'color' => $service['color'],
        ];
    }
}
