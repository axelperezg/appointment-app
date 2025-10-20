<?php

namespace Tests\Feature\Service;

use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_services(): void
    {
        // Arrange
        $services = Service::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(ListServices::class)
            ->assertCanSeeTableRecords($services);
    }

    public function test_can_search_services(): void
    {
        // Arrange
        $services = Service::factory()->count(3)->create();
        $serviceToFind = $services->first();

        // Act & Assert
        Livewire::test(ListServices::class)
            ->searchTable($serviceToFind->name)
            ->assertCanSeeTableRecords([$serviceToFind])
            ->assertCanNotSeeTableRecords($services->skip(1));
    }
}
