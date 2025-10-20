<?php

namespace Tests\Feature;

use App\Filament\Pages\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    public function test_calendar_page_can_be_rendered(): void
    {
        // Arrange
        // No se requiere configuración especial

        // Act & Assert
        Livewire::test(Calendar::class)
            ->assertSuccessful();
    }
}
