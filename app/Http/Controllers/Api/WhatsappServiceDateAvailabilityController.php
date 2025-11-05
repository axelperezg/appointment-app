<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsappServiceDateAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $serviceId = $request->input('service_id');
        $date = $request->input('date');

        try {
            return $this->getServiceDateAvailability($serviceId, $date);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function getServiceDateAvailability($serviceId, $date)
    {
        // Validate inputs
        if (! $serviceId || ! $date) {
            return response()->json([
                'error' => 'The service_id and date parameters are required.',
            ], 422);
        }

        // Fetch the service and its employees with necessary relationships
        $service = Service::with([
            'employees.schedules',
            'employees.appointments',
        ])->find($serviceId);

        if (! $service) {
            return response()->json([
                'error' => 'Service not found.',
            ], 404);
        }

        $employees = $service->employees;

        // Prepare availability array
        $availability = [];

        foreach ($employees as $employee) {
            // Get the employee schedule for the given date filtering only available hours
            $hours = $employee->getScheduleByDayAndService($date, $service, $employee);

            foreach ($hours as $hourData) {
                // Only consider hours that are available
                if (! $hourData['is_available']) {
                    continue;
                }

                // Ensure we are still on the requested date (the helper may return next day if today has no schedule)
                $hourCarbon = Carbon::parse($hourData['hour'])->setTimezone(config('app.timezone'));
                if ($hourCarbon->format('Y-m-d') !== Carbon::parse($date)->format('Y-m-d')) {
                    continue;
                }

                $hourKey = $hourCarbon->format('H:i');

                if (! isset($availability[$hourKey])) {
                    $availability[$hourKey] = [
                        'employees' => [],
                    ];
                }

                $availability[$hourKey]['employees'][] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                ];
            }
        }

        // Sort hours chronologically
        ksort($availability);

        return response()->json($availability);
    }
}

