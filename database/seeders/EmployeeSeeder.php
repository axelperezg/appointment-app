<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar empleados existentes
        Employee::query()->delete();

        // Crear 5 empleados
        $employees = [
            ['name' => 'Ana García', 'email' => 'ana@ejemplo.com', 'phone_number' => '555-0001'],
            ['name' => 'Carlos Ruiz', 'email' => 'carlos@ejemplo.com', 'phone_number' => '555-0002'],
            ['name' => 'María López', 'email' => 'maria@ejemplo.com', 'phone_number' => '555-0003'],
            ['name' => 'José Martínez', 'email' => 'jose@ejemplo.com', 'phone_number' => '555-0004'],
            ['name' => 'Laura Sánchez', 'email' => 'laura@ejemplo.com', 'phone_number' => '555-0005'],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}
