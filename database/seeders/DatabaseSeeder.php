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
            ['name' => 'Psicoterapia', 'duration' => 60, 'price' => 80.00, 'color' => '#9B59B6'],
            ['name' => 'Terapia de Pareja', 'duration' => 90, 'price' => 120.00, 'color' => '#E74C3C'],
            ['name' => 'Terapia Familiar', 'duration' => 90, 'price' => 150.00, 'color' => '#3498DB'],
            ['name' => 'Terapia Infantil y Juvenil', 'duration' => 60, 'price' => 70.00, 'color' => '#F39C12'],
            ['name' => 'Orientación Vocacional', 'duration' => 60, 'price' => 60.00, 'color' => '#1ABC9C'],
            ['name' => 'Coaching (de Vida, Ejecutivo, Empresarial)', 'duration' => 60, 'price' => 100.00, 'color' => '#34495E'],
            ['name' => 'Evaluaciones Psicológicas', 'duration' => 120, 'price' => 150.00, 'color' => '#16A085'],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Create single employee
        $employee = Employee::factory()->create([
            'name' => 'psiconeurocoaching',
            'email' => 'psiconeurocoaching@example.com',
            'phone_number' => '+1234567890',
        ]);

        $employees = collect([$employee]);

        // Assign all services to the employee
        $allServices = Service::all();
        foreach ($allServices as $service) {
            $employee->services()->attach($service->id);
        }

        // Create clients
        $clients = Client::factory(20)->create();

        // Configure work schedule for the employee (Monday to Friday)
        foreach ([1, 2, 3, 4, 5] as $day) {
            $schedule = $employee->schedules()->where('day', $day)->first();
            if ($schedule) {
                $schedule->update([
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                ]);
            }
        }

        // Create appointments for TODAY
        $today = now()->startOfDay();
        $todayDayOfWeek = now()->dayOfWeek === 0 ? 7 : now()->dayOfWeek; // Convert Sunday from 0 to 7
        
        // Get employees working today
        $workingEmployeesToday = $employees->filter(function ($employee) use ($todayDayOfWeek) {
            $schedule = $employee->schedules()->where('day', $todayDayOfWeek)->first();
            return $schedule && $schedule->hasSchedule();
        });

        if ($workingEmployeesToday->isNotEmpty()) {
            // Create 6 appointments throughout the day for today
            $timeSlots = [
                ['09:00', '10:00'],
                ['10:30', '11:30'],
                ['12:00', '13:00'],
                ['14:00', '15:00'],
                ['15:30', '16:30'],
                ['17:00', '18:00'],
            ];

            foreach ($timeSlots as $slot) {
                $employee = $workingEmployeesToday->first();
                $service = $employee->services->random();
                $client = $clients->random();

                Appointment::create([
                    'employee_id' => $employee->id,
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'starts_at' => $today->copy()->setTimeFromTimeString($slot[0]),
                    'ends_at' => $today->copy()->setTimeFromTimeString($slot[1]),
                    'note' => 'Cita de prueba para hoy - ' . $service->name,
                ]);
            }
        }
    }
}
