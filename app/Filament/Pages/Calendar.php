<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppointmentCalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Calendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario';

    protected string $view = 'filament.pages.calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentCalendarWidget::class,
        ];
    }
}
