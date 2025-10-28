<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\RelationManagers\ServicesRelationManager;
use App\Models\Employee;
use App\Models\Service;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageEmployeeServicesTest extends TestCase
{
    public function test_can_list_employee_services_in_relation_manager(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $services = Service::factory()->count(3)->create();

        // Attach services to employee
        foreach ($services as $service) {
            $employee->services()->attach($service);
        }

        // Act & Assert
        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->assertCanSeeTableRecords($services)
            ->assertCountTableRecords(3);
    }

    public function test_can_attach_a_service_to_an_employee(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Haircut',
            'price' => 50.00,
        ]);

        // Act
        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->callAction(TestAction::make(AttachAction::class)->table(), [
                'recordId' => $service->id,
            ])
            ->assertNotified();

        // Assert
        $this->assertDatabaseHas('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_can_detach_a_service_from_an_employee(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();

        $employee->services()->attach($service);

        $this->assertDatabaseHas('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service->id,
        ]);

        // Act
        $employee->services()->detach($service);

        // Assert
        $this->assertDatabaseMissing('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_displays_service_price_in_the_table(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Massage',
            'price' => 80.00,
        ]);

        $employee->services()->attach($service);

        // Act & Assert
        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->assertCanSeeTableRecords([$service]);

        // Verify the relationship is stored correctly in database
        $this->assertDatabaseHas('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_can_attach_multiple_different_services_to_an_employee(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $service1 = Service::factory()->create(['name' => 'Service 1']);
        $service2 = Service::factory()->create(['name' => 'Service 2']);

        // Act
        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->callAction(TestAction::make(AttachAction::class)->table(), [
                'recordId' => $service1->id,
            ])
            ->callAction(TestAction::make(AttachAction::class)->table(), [
                'recordId' => $service2->id,
            ]);

        // Assert
        $this->assertDatabaseHas('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service1->id,
        ]);

        $this->assertDatabaseHas('employee_service', [
            'employee_id' => $employee->id,
            'service_id' => $service2->id,
        ]);

        $this->assertEquals(2, $employee->services()->count());
    }

    public function test_displays_service_details_in_the_table(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Premium Haircut',
            'duration' => 45,
            'color' => '#FF5733',
        ]);

        $employee->services()->attach($service);

        // Act & Assert
        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->assertCanSeeTableRecords([$service])
            ->assertTableColumnStateSet('name', 'Premium Haircut', $service)
            ->assertTableColumnStateSet('duration', 45, $service)
            ->assertTableColumnStateSet('color', '#FF5733', $service);
    }
}

