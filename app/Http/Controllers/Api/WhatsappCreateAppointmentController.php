<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Rules\PreventOverlappingAppointmentsRule;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WhatsappCreateAppointmentController extends Controller
{
    public function __invoke(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i',
                'phone_number' => 'required|string',
                'name' => 'required|string',
                'email' => 'nullable|email',
                'note' => 'nullable|string',
            ]);

            $service = Service::findOrFail($request->service_id);
            $employee = Employee::findOrFail($request->employee_id);
            $timezone = config('app.timezone', 'America/Guayaquil');
            // Parse the date/time in local timezone, then convert to UTC for storage
            $dateTimeLocal = CarbonImmutable::parse("$request->date $request->time", $timezone);
            $dateTime = $dateTimeLocal->utc();
            $phoneNumber = $request->phone_number;

            // Calcular la hora de fin de la cita
            $endTime = $dateTime->copy()->addMinutes($service->duration);

            // Validar que no haya citas superpuestas
            $validator = Validator::make($request->all(), [
                'employee_id' => [
                    new PreventOverlappingAppointmentsRule(
                        $employee->id,
                        $dateTimeLocal->format('H:i'),
                        $endTime->setTimezone($timezone)->format('H:i'),
                        $request->date
                    ),
                ],
            ]);

            if ($validator->fails()) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Lo sentimos, al parecer ya alguien reservó la cita a la misma hora o interfiere con otra cita.',
                    'error' => $validator->errors()->first('employee_id'),
                ], 200);
            }

            $client = Client::query()->updateOrCreate([
                'phone_number' => $phoneNumber,
            ], [
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $appointment = Appointment::create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'employee_id' => $employee->id,
                'starts_at' => $dateTime,
                'ends_at' => $endTime,
                'note' => $request->note,
            ]);

            DB::commit();

            // Set locale to Spanish for date formatting
            $originalLocale = app()->getLocale();
            app()->setLocale('es');
            CarbonImmutable::setLocale('es');

            // Convert from UTC (stored in DB) to local timezone
            // Get raw value from DB and parse as UTC, then convert to local timezone
            $startsAtRaw = $appointment->getRawOriginal('starts_at');
            $startsAtLocal = CarbonImmutable::parse($startsAtRaw, 'UTC')->setTimezone($timezone);

            $response = response()->json([
                'message' => 'Appointment created successfully',
                'data' => [
                    'phoneNumber' => $appointment->client->phone_number,
                    'clientName' => $appointment->client->name,
                    'appointmentDate' => $startsAtLocal->isoFormat('D [de] MMMM, YYYY'),
                    'appointmentTime' => $startsAtLocal->format('g:i A'),
                    'serviceName' => $appointment->service->name,
                    'employeeName' => $appointment->employee->name,
                ],
            ]);

            // Restore original locale
            app()->setLocale($originalLocale);
            CarbonImmutable::setLocale($originalLocale);

            return $response;
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Appointment creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

