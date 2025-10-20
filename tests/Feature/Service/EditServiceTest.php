<?php

namespace Tests\Feature\Service;

use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_edit_service(): void
    {
        // Arrange
        $service = Service::factory()->create();
        $newData = Service::factory()->make();

        // Act
        Livewire::test(EditService::class, ['record' => $service->id])
            ->fillForm([
                'name' => $newData->name,
                'duration' => $newData->duration,
                'price' => $newData->price,
                'color' => $newData->color,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $service->refresh();
        $this->assertEquals($newData->name, $service->name);
        $this->assertEquals($newData->duration, $service->duration);
        $this->assertEquals($newData->price, $service->price);
        $this->assertEquals($newData->color, $service->color);
    }

    public function test_required_fields_validation_on_edit(): void
    {
        // Arrange
        $service = Service::factory()->create();

        // Act & Assert
        Livewire::test(EditService::class, ['record' => $service->id])
            ->fillForm([
                'name' => '',
                'duration' => '',
                'price' => '',
                'color' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'duration' => 'required',
                'price' => 'required',
                'color' => 'required',
            ]);
    }
}
