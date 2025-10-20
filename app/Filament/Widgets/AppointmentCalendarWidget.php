<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Employee;
use Carbon\WeekDay;
use Guava\Calendar\Actions\CreateAction;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AppointmentCalendarWidget extends CalendarWidget
{
    protected static bool $isDiscovered = false;

    protected CalendarViewType $calendarView = CalendarViewType::ResourceTimeGridDay;

    protected ?string $locale = 'es';

    protected WeekDay $firstDay = WeekDay::Monday; // Lunes

    protected bool $dateClickEnabled = false;

    protected bool $eventClickEnabled = false;

    protected bool $eventResizeEnabled = false;

    protected bool $eventDragEnabled = false;

    protected function getEvents(FetchInfo $info): Collection|Builder
    {
        return Appointment::query()
            ->with(['client', 'service', 'employee'])
            ->where('ends_at', '>=', $info->start)
            ->where('starts_at', '<=', $info->end)
            ->get();
    }

    protected function getResources(): Collection|Builder
    {
        return Employee::query()->get();
    }

    protected function getViewOptions(): array
    {
        return [
            CalendarViewType::ResourceTimeGridDay->value => [
                'titleFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                    'day' => 'numeric',
                ],
                'slotMinTime' => '08:00:00',
                'slotMaxTime' => '18:00:00',
            ],
            CalendarViewType::ResourceTimeGridWeek->value => [
                'titleFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                ],
                'slotMinTime' => '08:00:00',
                'slotMaxTime' => '18:00:00',
            ],
            CalendarViewType::DayGridMonth->value => [
                'titleFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                ],
            ],
        ];
    }

    public function getHeaderActions(): array
    {
        return [];
    }
}
