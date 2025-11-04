<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Employee;
use Carbon\Carbon;
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

    protected ?string $timezone = 'America/Guayaquil';

    protected bool $dateClickEnabled = true;

    protected bool $eventClickEnabled = false;

    protected bool $eventResizeEnabled = false;

    protected bool $eventDragEnabled = false;

    protected $listeners = [
        'refreshCalendar' => 'refreshCalendar',
    ];

    public function refreshCalendar(): void
    {
        $this->refreshRecords();
        $this->refreshResources();
    }

    public function onDateClickJs(array $data): void
    {
        // Extract date, time, and resource (employee) from the click event (original date is in UTC)
        $dateUtc = Carbon::parse($data['date'], 'UTC');
        $date = $dateUtc->setTimezone(config('app.timezone'));

        $clickedTime = $date->format('H:i:00');

        $this->dispatch('open-create-modal', [
            'date' => $date->format('Y-m-d'),
            'start_time' => $clickedTime
        ]);
    }

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

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '18:00:00',
            'allDaySlot' => false,
        ];
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
            ],
            CalendarViewType::ResourceTimeGridWeek->value => [
                'titleFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                ],
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
