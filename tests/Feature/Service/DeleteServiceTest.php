<?php

namespace Tests\Feature\Service;

use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_service(): void
    {
        // Arrange
        $service = Service::factory()->create();

        // Act
        Livewire::test(ListServices::class)
            ->callTableAction('delete', $service)
            ->assertSuccessful();

        // Assert
        $this->assertCount(0, Service::all());
    }
}
