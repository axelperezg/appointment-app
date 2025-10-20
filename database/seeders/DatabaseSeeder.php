<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user for Filament
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'miguelbonifaz126@gmail.com',
        ]);

        // Create services
        $services = [
            ['name' => 'Haircut', 'duration' => 30, 'price' => 25.00, 'color' => '#FF6B6B'],
            ['name' => 'Hair Coloring', 'duration' => 120, 'price' => 85.00, 'color' => '#4ECDC4'],
            ['name' => 'Manicure', 'duration' => 45, 'price' => 30.00, 'color' => '#45B7D1'],
            ['name' => 'Pedicure', 'duration' => 60, 'price' => 45.00, 'color' => '#FFA07A'],
            ['name' => 'Massage Therapy', 'duration' => 60, 'price' => 70.00, 'color' => '#98D8C8'],
            ['name' => 'Facial Treatment', 'duration' => 90, 'price' => 95.00, 'color' => '#F7DC6F'],
            ['name' => 'Consultation', 'duration' => 30, 'price' => 20.00, 'color' => '#BB8FCE'],
            ['name' => 'Beard Trim', 'duration' => 20, 'price' => 15.00, 'color' => '#85C1E2'],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Create employees with their schedules (automatically created via model event)
        $employees = Employee::factory(5)->create();

        // Assign services to employees
        $allServices = Service::all();
        foreach ($employees as $employee) {
            // Each employee gets 2-4 random services
            $servicesToAttach = $allServices->random(rand(2, 4));
            
            foreach ($servicesToAttach as $service) {
                $employee->services()->attach($service->id, [
                    'price' => $service->price + rand(-10, 10), // Slight price variation per employee
                ]);
            }
        }

        // Create clients
        Client::factory(20)->create();

        // Create some appointments
        Appointment::factory(15)->create();
    }
}
