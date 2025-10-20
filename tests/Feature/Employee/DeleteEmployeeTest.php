<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_employee(): void
    {
        // Arrange
        $employee = Employee::factory()->create();

        // Act
        Livewire::test(ListEmployees::class)
            ->callTableAction('delete', $employee)
            ->assertSuccessful();

        // Assert
        $this->assertCount(0, Employee::all());
    }
}
