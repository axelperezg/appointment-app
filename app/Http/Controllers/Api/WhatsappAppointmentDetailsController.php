<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsappAppointmentDetailsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
        ]);

        $phoneNumber = $request->phone_number;
        $timezone = config('app.timezone', 'America/Guayaquil');

        // Set locale to Spanish for date formatting
        $originalLocale = app()->getLocale();
        app()->setLocale('es');
        Carbon::setLocale('es');

        $appointments = Appointment::whereHas('client', function ($query) use ($phoneNumber) {
            $query->where('phone_number', $phoneNumber);
        })
            ->whereDate('starts_at', '>=', today())
            ->with(['client', 'service', 'employee'])
            ->orderBy('starts_at', 'asc')
            ->get()
            ->map(function ($appointment) use ($timezone) {
                // Convert from UTC (stored in DB) to local timezone
                // Get raw value from DB and parse as UTC, then convert to local timezone
                $startsAtRaw = $appointment->getRawOriginal('starts_at');
                $startsAtLocal = Carbon::parse($startsAtRaw, 'UTC')->setTimezone($timezone);
                return [
                    'id' => $appointment->id,
                    'clientName' => $appointment->client->name,
                    'appointmentDate' => $startsAtLocal->isoFormat('D [de] MMMM, YYYY'),
                    'appointmentTime' => $startsAtLocal->format('g:i A'),
                    'serviceName' => $appointment->service->name,
                    'employeeName' => $appointment->employee->name,
                    'serviceId' => $appointment->service->id,
                    'employeeId' => $appointment->employee->id,
                ];
            });

        // Restore original locale
        app()->setLocale($originalLocale);
        Carbon::setLocale($originalLocale);

        if ($appointments->isEmpty()) {
            return response()->json([
                'message' => 'No appointments found',
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => 'Appointments retrieved successfully',
            'data' => $appointments,
        ]);
    }
}

