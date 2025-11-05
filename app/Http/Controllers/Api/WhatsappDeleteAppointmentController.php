<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class WhatsappDeleteAppointmentController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $appointment = Appointment::with(['service', 'employee'])
            ->findOrFail($request->appointment_id);

        $appointmentData = [
            'service_id' => $appointment->service->id,
            'service_name' => $appointment->service->name,
            'employee_id' => $appointment->employee->id,
            'employee_name' => $appointment->employee->name,
        ];

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully',
            'data' => $appointmentData,
        ]);
    }
}

