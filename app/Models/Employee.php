<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Employee extends Model implements Resourceable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'minimum_advance_booking',
    ];

    protected static function booted(): void
    {
        static::created(function (Employee $employee) {
            foreach ([1, 2, 3, 4, 5, 6, 7] as $day) {
                $schedule = Schedule::create([
                    'employee_id' => $employee->id,
                    'day' => $day,
                    'start_time' => null,
                    'end_time' => null,
                ]);

                RestSchedule::create([
                    'schedule_id' => $schedule->id,
                    'start_time' => null,
                    'end_time' => null,
                ]);
            }
        });
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class)->orderBy('day');
    }

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->name);
    }

    public function getScheduleByDayAndService(string $date, Service $service, Employee $employee, bool $withAllHours = false): Collection
    {
        $targetDate = Carbon::parse($date, config('app.timezone'));

        $hours = collect();

        $i = 0;
        $schedule = null;

        while ($hours->isEmpty()) {
            $schedule = $this->schedules()
                ->where('day', $targetDate->dayOfWeekIso)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->first();

            $hours = $this->getHours($schedule, $targetDate, $service, $employee, $withAllHours);
            $targetDate->addDay();
            $i++;

            if ($i >= 7) {
                break;
            }
        }

        return $hours;
    }

    private function isHourAvailable(int $employeeId, Carbon $startsAt, Carbon $endsAt): bool
    {
        $appointmentExists = Appointment::query()
            ->where('employee_id', $employeeId)
            ->overlapping($startsAt, $endsAt)
            ->exists();

        return !$appointmentExists;
    }

    private function getHours(
        ?Schedule $schedule,
        Carbon $date,
        Service $service,
        Employee $employee,
        bool $withFullHours
    ): Collection {
        if ($schedule === null) {
            return collect();
        }

        $isToday = $date->isToday();

        $start = $schedule->start_time->setDateFrom($date);
        $end = $schedule->end_time->setDateFrom($date);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return collect(
            CarbonPeriod::since($start)
                ->minutes(30)
                ->until($end->copy()->subMinutes($service->duration))
                ->toArray()
        )
            ->map(function (Carbon $date) use ($employee, $service) {
                $startsAt = $date;
                $endsAt = $date->copy()->addMinutes($service->duration);

                $isAvailable = $this->isHourAvailable($employee->id, $startsAt, $endsAt);

                return [
                    'hour' => $date->toIso8601String(),
                    'is_available' => $isAvailable,
                ];
            })
            ->reject(function ($data) use ($withFullHours, $isToday) {
                if ($withFullHours === true) {
                    return false;
                }

                return $isToday && Carbon::parse($data['hour'])->lessThan(now());
            })
            ->reject(function ($data) use ($isToday, $employee) {
                $minimum_advance_booking = $employee->minimum_advance_booking ?? 0;

                if (!$isToday) {
                    return false;
                }

                if (Carbon::parse($data['hour'])->format('H:i') < now()->addMinutes($minimum_advance_booking)->format('H:i')) {
                    return true;
                }

                return false;
            })
            ->reject(function ($data) use ($schedule) {
                if (!$schedule->restSchedule || !$schedule->restSchedule->hasRestSchedule()) {
                    return false;
                }

                $date = Carbon::parse($data['hour']);
                $startTime = $schedule->restSchedule->start_time->setDateFrom($date);
                $endTime = $schedule->restSchedule->end_time->setDateFrom($date)->subMinute();

                return $date->isBetween($startTime, $endTime);
            })
            ->map(function ($data) use ($service, $schedule) {
                if (!$schedule->restSchedule || !$schedule->restSchedule->hasRestSchedule()) {
                    return $data;
                }

                $date = CarbonImmutable::parse($data['hour']);
                $startTime = $schedule->restSchedule->start_time->subMinutes($service->duration)->addMinute()->setDateFrom($date);
                $endTime = $schedule->restSchedule->start_time->setDateFrom($date);

                if ($date->isBetween($startTime, $endTime)) {
                    $data['is_available'] = false;
                }

                return $data;
            });
    }
}
