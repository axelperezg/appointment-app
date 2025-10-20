<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_employees(): void
    {
        // Arrange
        $employees = Employee::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(ListEmployees::class)
            ->assertCanSeeTableRecords($employees);
    }

    public function test_can_search_employees(): void
    {
        // Arrange
        $employees = Employee::factory()->count(3)->create();
        $employeeToFind = $employees->first();

        // Act & Assert
        Livewire::test(ListEmployees::class)
            ->searchTable($employeeToFind->name)
            ->assertCanSeeTableRecords([$employeeToFind])
            ->assertCanNotSeeTableRecords($employees->skip(1));
    }
}
