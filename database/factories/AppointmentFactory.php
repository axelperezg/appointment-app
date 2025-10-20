<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+30 days');
        $service = Service::inRandomOrder()->first() ?? Service::factory()->create();
        
        return [
            'client_id' => Client::factory(),
            'service_id' => $service->id,
            'employee_id' => Employee::factory(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify("+{$service->duration} minutes"),
            'note' => fake()->optional(0.3)->sentence(),
        ];
    }
}
