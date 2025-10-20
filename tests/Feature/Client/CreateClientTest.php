<?php

namespace Tests\Feature\Client;

use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_client(): void
    {
        // Arrange
        $data = Client::factory()->make();

        // Act
        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => $data->name,
                'email' => $data->email,
                'phone_number' => $data->phone_number,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertCount(1, Client::all());
        $client = Client::first();
        $this->assertEquals($data->name, $client->name);
        $this->assertEquals($data->email, $client->email);
        $this->assertEquals($data->phone_number, $client->phone_number);
    }

    public function test_required_fields_validation(): void
    {
        // Arrange
        // No se necesita configuración

        // Act & Assert
        Livewire::test(CreateClient::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'phone_number' => 'required',
            ]);
    }
}
