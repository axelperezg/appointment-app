<?php

namespace Tests\Feature\Client;

use App\Filament\Resources\Clients\Pages\EditClient;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_edit_client(): void
    {
        // Arrange
        $client = Client::factory()->create();
        $newData = Client::factory()->make();

        // Act
        Livewire::test(EditClient::class, ['record' => $client->id])
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
                'phone_number' => $newData->phone_number,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $client->refresh();
        $this->assertEquals($newData->name, $client->name);
        $this->assertEquals($newData->email, $client->email);
        $this->assertEquals($newData->phone_number, $client->phone_number);
    }

    public function test_required_fields_validation_on_edit(): void
    {
        // Arrange
        $client = Client::factory()->create();

        // Act & Assert
        Livewire::test(EditClient::class, ['record' => $client->id])
            ->fillForm([
                'name' => '',
                'phone_number' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'phone_number' => 'required',
            ]);
    }
}
