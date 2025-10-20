<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
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
        return $this->belongsToMany(Service::class)->withPivot('price')->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class)->orderBy('day');
    }
}
