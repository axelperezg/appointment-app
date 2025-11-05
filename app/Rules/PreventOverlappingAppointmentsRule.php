<?php

namespace App\Rules;

use App\Models\Appointment;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;

class PreventOverlappingAppointmentsRule implements ValidationRule
{
    public function __construct(
        public ?int $employeeId,
        public ?string $startHour,
        public ?string $endHour,
        public ?string $date,
        public ?int $appointmentId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->appointmentExists()) {
            $fail('Lo sentimos, al parecer ya alguien reservó la cita a la misma hora o interfiere con otra cita.');
        }
    }

    private function appointmentExists(): bool
    {
        $timezone = config('app.timezone');
        $startsAt = Carbon::parse("$this->date $this->startHour", $timezone);
        $endsAt = Carbon::parse("$this->date $this->endHour", $timezone);

        return Appointment::query()
            ->overlapping($startsAt, $endsAt)
            ->when($this->appointmentId, fn (Builder $query) => $query->where('id', '!=', $this->appointmentId))
            ->where('employee_id', $this->employeeId)
            ->exists();
    }
}

