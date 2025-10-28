<?php

namespace Tests\Feature\Employee;

use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\RelationManagers\SchedulesRelationManager;
use App\Models\Employee;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageEmployeeSchedulesTest extends TestCase
{
    public function test_employee_automatically_has_7_schedules_created(): void
    {
        // Arrange & Act
        $employee = Employee::factory()->create();

        // Assert
        $this->assertEquals(7, $employee->schedules()->count());
        
        // Verify all days 1-7 are present
        for ($day = 1; $day <= 7; $day++) {
            $this->assertDatabaseHas('schedules', [
                'employee_id' => $employee->id,
                'day' => $day,
            ]);
        }
    }

    public function test_can_list_all_7_schedules_in_relation_manager(): void
    {
        // Arrange
        $employee = Employee::factory()->create();

        // Act & Assert
        Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => EditEmployee::class,
        ])
            ->assertCanSeeTableRecords($employee->schedules)
            ->assertCountTableRecords(7);
    }

    public function test_schedules_are_initially_created_without_times(): void
    {
        // Arrange
        $employee = Employee::factory()->create();

        // Assert
        foreach ($employee->schedules as $schedule) {
            $this->assertNull($schedule->start_time);
            $this->assertNull($schedule->end_time);
            $this->assertFalse($schedule->hasSchedule());
        }
    }

    public function test_can_edit_schedule_to_add_work_hours(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $schedule = $employee->schedules()->where('day', 1)->first();

        // Act
        $schedule->update([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        // Assert
        $schedule->refresh();
        $this->assertNotNull($schedule->start_time);
        $this->assertNotNull($schedule->end_time);
        $this->assertTrue($schedule->hasSchedule());
    }

    public function test_can_clear_schedule_to_remove_work_hours(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $schedule = $employee->schedules()->where('day', 1)->first();
        $schedule->update([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->assertTrue($schedule->hasSchedule());

        // Act - Clear the schedule
        $schedule->update([
            'start_time' => null,
            'end_time' => null,
        ]);

        // Assert
        $schedule->refresh();
        $this->assertNull($schedule->start_time);
        $this->assertNull($schedule->end_time);
        $this->assertFalse($schedule->hasSchedule());
    }

    public function test_schedules_are_sorted_by_day(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $schedules = $employee->schedules;

        // Assert - schedules should be in order from day 1 to 7
        $this->assertEquals(1, $schedules->first()->day);
        $this->assertEquals(7, $schedules->last()->day);
        
        // Verify they're in ascending order
        $previousDay = 0;
        foreach ($schedules as $schedule) {
            $this->assertGreaterThan($previousDay, $schedule->day);
            $previousDay = $schedule->day;
        }
    }

    public function test_can_set_different_hours_for_different_days(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $monday = $employee->schedules()->where('day', 1)->first();
        $friday = $employee->schedules()->where('day', 5)->first();

        // Act
        $monday->update([
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $friday->update([
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        // Assert
        $monday->refresh();
        $friday->refresh();
        
        $this->assertEquals('08:00', $monday->start_time->format('H:i'));
        $this->assertEquals('16:00', $monday->end_time->format('H:i'));
        $this->assertEquals('10:00', $friday->start_time->format('H:i'));
        $this->assertEquals('18:00', $friday->end_time->format('H:i'));
    }

    public function test_has_schedule_method_works_correctly(): void
    {
        // Arrange
        $employee = Employee::factory()->create();
        $schedule = $employee->schedules()->where('day', 1)->first();

        // Initially should not have schedule
        $this->assertFalse($schedule->hasSchedule());

        // Set times
        $schedule->update([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        // Now should have schedule
        $this->assertTrue($schedule->hasSchedule());
    }
}

