<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model implements Eventable
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_id',
        'employee_id',
        'starts_at',
        'ends_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->client->name . ' - ' . $this->service->name)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->resourceId($this->employee_id);
    }

    public function scopeOverlapping(Builder $query, $startsAt, $endsAt): Builder
    {
        return $query->where(function ($q) use ($startsAt, $endsAt) {
            // New appointment starts before existing ends AND ends after existing starts
            $q->where('starts_at', '<', $endsAt)
              ->where('ends_at', '>', $startsAt);
        });
    }
}
