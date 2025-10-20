<?php

namespace Tests\Feature\Client;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_client(): void
    {
        // Arrange
        $client = Client::factory()->create();

        // Act
        Livewire::test(ListClients::class)
            ->callTableAction('delete', $client)
            ->assertSuccessful();

        // Assert
        $this->assertCount(0, Client::all());
    }
}
