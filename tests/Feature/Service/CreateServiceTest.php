<?php

namespace Tests\Feature\Service;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateServiceTest extends TestCase
{
    public function test_can_create_service(): void
    {
        // Arrange
        $data = Service::factory()->make();

        // Act
        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $data->name,
                'duration' => $data->duration,
                'price' => $data->price,
                'color' => $data->color,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertCount(1, Service::all());
        $service = Service::first();
        $this->assertEquals($data->name, $service->name);
        $this->assertEquals($data->duration, $service->duration);
        $this->assertEquals($data->price, $service->price);
        $this->assertEquals($data->color, $service->color);
    }

    public function test_required_fields_validation(): void
    {
        // Arrange
        // No se necesita configuración

        // Act & Assert
        Livewire::test(CreateService::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'duration' => 'required',
                'price' => 'required',
                'color' => 'required',
            ]);
    }
}
