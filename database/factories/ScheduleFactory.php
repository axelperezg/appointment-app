<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 12);
        $endHour = fake()->numberBetween(16, 20);

        return [
            'employee_id' => Employee::factory(),
            'day' => fake()->numberBetween(1, 7),
            'start_time' => now()->setTime($startHour, 0, 0),
            'end_time' => now()->setTime($endHour, 0, 0),
        ];
    }

    public function dayOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => null,
            'end_time' => null,
        ]);
    }
}
