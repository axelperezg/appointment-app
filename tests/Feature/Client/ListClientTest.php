<?php

namespace Tests\Feature\Client;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_clients(): void
    {
        // Arrange
        $clients = Client::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(ListClients::class)
            ->assertCanSeeTableRecords($clients);
    }

    public function test_can_search_clients(): void
    {
        // Arrange
        $clients = Client::factory()->count(3)->create();
        $clientToFind = $clients->first();

        // Act & Assert
        Livewire::test(ListClients::class)
            ->searchTable($clientToFind->name)
            ->assertCanSeeTableRecords([$clientToFind])
            ->assertCanNotSeeTableRecords($clients->skip(1));
    }
}
