<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar citas existentes
        Appointment::query()->delete();
        Client::query()->delete();
        Service::query()->delete();

        // Crear algunos servicios de ejemplo
        $services = [
            ['name' => 'Corte de Cabello', 'duration' => 30, 'price' => 25.00, 'color' => '#3B82F6'],
            ['name' => 'Manicure', 'duration' => 45, 'price' => 35.00, 'color' => '#EC4899'],
            ['name' => 'Masaje', 'duration' => 60, 'price' => 60.00, 'color' => '#10B981'],
            ['name' => 'Facial', 'duration' => 50, 'price' => 45.00, 'color' => '#F59E0B'],
            ['name' => 'Pedicure', 'duration' => 40, 'price' => 30.00, 'color' => '#8B5CF6'],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Crear algunos clientes de ejemplo
        $clients = [
            ['name' => 'Pedro González', 'email' => 'pedro@ejemplo.com', 'phone_number' => '555-1001'],
            ['name' => 'Sofía Ramírez', 'email' => 'sofia@ejemplo.com', 'phone_number' => '555-1002'],
            ['name' => 'Miguel Torres', 'email' => 'miguel@ejemplo.com', 'phone_number' => '555-1003'],
            ['name' => 'Carmen Díaz', 'email' => 'carmen@ejemplo.com', 'phone_number' => '555-1004'],
            ['name' => 'Roberto Pérez', 'email' => 'roberto@ejemplo.com', 'phone_number' => '555-1005'],
            ['name' => 'Elena Morales', 'email' => 'elena@ejemplo.com', 'phone_number' => '555-1006'],
            ['name' => 'Francisco Vega', 'email' => 'francisco@ejemplo.com', 'phone_number' => '555-1007'],
            ['name' => 'Isabel Castro', 'email' => 'isabel@ejemplo.com', 'phone_number' => '555-1008'],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }

        // Obtener todos los empleados, servicios y clientes
        $employees = Employee::all();
        $services = Service::all();
        $clients = Client::all();

        // Crear citas para hoy y mañana
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $timeSlots = [
            ['08:00', '09:00'],
            ['09:00', '10:00'],
            ['10:00', '11:00'],
            ['11:00', '12:00'],
            ['12:00', '13:00'],
            ['13:00', '14:00'],
            ['14:00', '15:00'],
            ['15:00', '16:00'],
            ['16:00', '17:00'],
            ['17:00', '18:00'],
        ];

        // Crear citas para cada empleado
        foreach ($employees as $employee) {
            // Citas para hoy
            $usedSlots = [];
            $appointmentsToday = rand(3, 6); // 3-6 citas por empleado hoy

            for ($i = 0; $i < $appointmentsToday; $i++) {
                $slotIndex = rand(0, count($timeSlots) - 1);

                // Evitar duplicar slots
                while (in_array($slotIndex, $usedSlots)) {
                    $slotIndex = rand(0, count($timeSlots) - 1);
                }
                $usedSlots[] = $slotIndex;

                $slot = $timeSlots[$slotIndex];
                $service = $services->random();

                Appointment::create([
                    'client_id' => $clients->random()->id,
                    'service_id' => $service->id,
                    'employee_id' => $employee->id,
                    'starts_at' => $today->copy()->setTimeFromTimeString($slot[0]),
                    'ends_at' => $today->copy()->setTimeFromTimeString($slot[1]),
                    'note' => null,
                ]);
            }

            // Citas para mañana
            $usedSlots = [];
            $appointmentsTomorrow = rand(3, 6); // 3-6 citas por empleado mañana

            for ($i = 0; $i < $appointmentsTomorrow; $i++) {
                $slotIndex = rand(0, count($timeSlots) - 1);

                // Evitar duplicar slots
                while (in_array($slotIndex, $usedSlots)) {
                    $slotIndex = rand(0, count($timeSlots) - 1);
                }
                $usedSlots[] = $slotIndex;

                $slot = $timeSlots[$slotIndex];
                $service = $services->random();

                Appointment::create([
                    'client_id' => $clients->random()->id,
                    'service_id' => $service->id,
                    'employee_id' => $employee->id,
                    'starts_at' => $tomorrow->copy()->setTimeFromTimeString($slot[0]),
                    'ends_at' => $tomorrow->copy()->setTimeFromTimeString($slot[1]),
                    'note' => null,
                ]);
            }
        }
    }
}
