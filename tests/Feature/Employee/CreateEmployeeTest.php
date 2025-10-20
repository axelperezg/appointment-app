<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateEmployeeTest extends TestCase
{
    public function test_can_create_employee(): void
    {
        // Arrange
        $data = Employee::factory()->make();

        // Act
        Livewire::test(CreateEmployee::class)
            ->fillForm( [
                'name' => $data->name,
                'email' => $data->email,
                'phone_number' => $data->phone_number,
                'minimum_advance_booking' => $data->minimum_advance_booking,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertCount(1, Employee::all());
        $employee = Employee::first();
        $this->assertEquals($data->name, $employee->name);
        $this->assertEquals($data->email, $employee->email);
        $this->assertEquals($data->phone_number, $employee->phone_number);
        $this->assertEquals($data->minimum_advance_booking, $employee->minimum_advance_booking);
    }

    public function test_required_fields_validation(): void
    {
        // Arrange
        // No se necesita configuración

        // Act & Assert
        Livewire::test(CreateEmployee::class)
            ->fillForm( [])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
            ]);
    }
}
