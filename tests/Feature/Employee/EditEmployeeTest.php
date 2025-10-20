<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_edit_employee(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $newData = Employee::factory()->make();

        // Act
        Livewire::test(EditEmployee::class, ['record' => $employee->id])
            ->fillForm( [
                'name' => $newData->name,
                'email' => $newData->email,
                'phone_number' => $newData->phone_number,
                'minimum_advance_booking' => $newData->minimum_advance_booking,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $employee->refresh();
        $this->assertEquals($newData->name, $employee->name);
        $this->assertEquals($newData->email, $employee->email);
        $this->assertEquals($newData->phone_number, $employee->phone_number);
        $this->assertEquals($newData->minimum_advance_booking, $employee->minimum_advance_booking);
    }

    public function test_required_fields_validation_on_edit(): void
    {
        // Arrange
        $employee = Employee::factory()->create();

        // Act & Assert
        Livewire::test(EditEmployee::class, ['record' => $employee->id])
            ->fillForm( [
                'name' => '',
                'email' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
            ]);
    }
}
