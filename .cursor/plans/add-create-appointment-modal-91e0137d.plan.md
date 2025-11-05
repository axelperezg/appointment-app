<!-- 91e0137d-e9ac-4ae4-8c36-a8a6aaaec082 f680bfd6-83be-4368-930f-398520d68da6 -->
# Plan: Create WhatsApp Agent APIs

## Overview

Create REST API endpoints to be consumed by a WhatsApp agent bot, following the structure from Ricardo Bonifaz project. The APIs will provide access to services, availability checking, and appointment management.

## Reference Project Analysis

Based on Ricardo Bonifaz project (`../ricardo-bonifaz/routes/api.php`):

- Uses Laravel Sanctum for API authentication (`auth:sanctum` middleware)
- APIs under `/api/whatsapp` prefix for WhatsApp-specific endpoints
- Key endpoints structure:

1. `GET /api/whatsapp/services` - List public services with employees
2. `POST /api/whatsapp/service-date-availability` - Get available time slots for a service on a date
3. `POST /api/whatsapp/create-appointment` - Create appointment
4. `GET /api/whatsapp/appointment-details` - Get client appointments by phone
5. `DELETE /api/whatsapp/delete-appointment` - Delete appointment

## Database Structure (Current Project)

- **services**: id, name, duration (minutes), price, color
- **employees**: id, name, email, phone_number, minimum_advance_booking
- **clients**: id, name, email, phone_number (unique)
- **appointments**: id, client_id, service_id, employee_id, starts_at, ends_at, note
- **schedules**: id, employee_id, day (1-7), start_time, end_time
- **rest_schedules**: id, schedule_id, start_time, end_time
- **employee_service**: pivot table (many-to-many)

## API Endpoints to Create

### 1. WhatsApp Services API

- **Route**: `GET /api/whatsapp/services`
- **Response**: `[{id, name, price, agent_description}]`
- **Logic**: Only services that have at least one employee assigned
- **Controller**: `WhatsappServiceController`

### 2. WhatsApp Service Date Availability API

- **Route**: `POST /api/whatsapp/service-date-availability`
- **Request**: `{service_id, date}` (date format: Y-m-d)
- **Response**: `{"HH:mm": {employees: [{id, name}]}}` - Available time slots grouped by hour
- **Logic**: 
- Gets all employees for the service
- For each employee, calculates available hours based on:
- Schedule for day of week
- Existing appointments (check overlaps)
- Rest periods
- Service duration
- Minimum advance booking time
- Current time (filter past hours for today)
- **Controller**: `WhatsappServiceDateAvailabilityController`

### 3. WhatsApp Create Appointment API

- **Route**: `POST /api/whatsapp/create-appointment`
- **Request**: `{service_id, employee_id, date, time, phone_number, name, email?, note?}`
- **Response**: `{message, data: {phoneNumber, clientName, appointmentDate, appointmentTime, serviceName, employeeName}}`
- **Logic**:
- Validates no overlapping appointments
- Creates/updates client by phone_number
- Calculates end time based on service duration
- Handles timezone conversion (America/Guayaquil ↔ UTC)
- Uses DB transaction
- **Controller**: `WhatsappCreateAppointmentController`

### 4. WhatsApp Appointment Details API

- **Route**: `GET /api/whatsapp/appointment-details?phone_number={phone}`
- **Response**: `{message, data: [{id, clientName, appointmentDate, appointmentTime, serviceName, employeeName, serviceId, employeeId}]}`
- **Logic**: Returns future appointments (starts_at >= today) for client by phone number
- **Controller**: `WhatsappAppointmentDetailsController`

### 5. WhatsApp Delete Appointment API

- **Route**: `DELETE /api/whatsapp/delete-appointment`
- **Request**: `{appointment_id}`
- **Response**: `{message, data: {service_id, service_name, employee_id, employee_name}}`
- **Controller**: `WhatsappDeleteAppointmentController`

## Implementation Steps

1. **Configure API Routes**

- Create `routes/api.php` file
- Update `bootstrap/app.php` to include API routes: `->withRouting(api: __DIR__.'/../routes/api.php')`
- Set up routes under `/api/whatsapp` prefix with authentication middleware

2. **Add Model Methods/Scopes**

- Add `scopeHasEmployees()` to Service model (filter services with employees)
- Add `getScheduleByDayAndService()` method to Employee model (calculate availability)
- Method should consider: schedules, appointments, rest periods, service duration, minimum advance booking

3. **Create Validation Rule**

- `App\Rules\PreventOverlappingAppointmentsRule` - Custom validation rule for appointment overlaps
- Reuse existing `scopeOverlapping` from Appointment model

4. **Create API Controllers**

- `App\Http\Controllers\Api\WhatsappServiceController` - List services
- `App\Http\Controllers\Api\WhatsappServiceDateAvailabilityController` - Get availability
- `App\Http\Controllers\Api\WhatsappCreateAppointmentController` - Create appointment
- `App\Http\Controllers\Api\WhatsappAppointmentDetailsController` - Get appointments
- `App\Http\Controllers\Api\WhatsappDeleteAppointmentController` - Delete appointment

5. **Handle Timezone Conversion**

- Ensure proper timezone handling (America/Guayaquil)
- Convert between UTC (database) and local timezone (API requests/responses)
- Use Carbon with timezone conversion

6. **Error Handling**

- Consistent JSON error responses
- Proper HTTP status codes (200, 400, 404, 422, 500)
- User-friendly error messages in Spanish

## Technical Details

- **Timezone**: America/Guayaquil (as per current project config)
- **Time slots**: 30-minute intervals (matching `getTimeOptions()` pattern)
- **Date format**: Y-m-d for dates in requests
- **Time format**: H:i for times in requests (24-hour format)
- **Response dates**: Formatted in Spanish (e.g., "d \d\e F, Y" = "11 de abril, 2025")
- **Response times**: Formatted as "g:i A" (e.g., "2:30 PM")
- **Transaction handling**: Use DB transactions for appointment creation
- **Overlap validation**: Reuse existing `scopeOverlapping` from Appointment model
- **Authentication**: Consider Laravel Sanctum (check if installed, if not, can use without auth initially)

## Files to Create/Modify

1. `routes/api.php` - New file
2. `bootstrap/app.php` - Add API routes configuration
3. `app/Http/Controllers/Api/WhatsappServiceController.php` - New
4. `app/Http/Controllers/Api/WhatsappServiceDateAvailabilityController.php` - New
5. `app/Http/Controllers/Api/WhatsappCreateAppointmentController.php` - New
6. `app/Http/Controllers/Api/WhatsappAppointmentDetailsController.php` - New
7. `app/Http/Controllers/Api/WhatsappDeleteAppointmentController.php` - New
8. `app/Rules/PreventOverlappingAppointmentsRule.php` - New
9. `app/Models/Service.php` - Add `scopeHasEmployees()` method
10. `app/Models/Employee.php` - Add `getScheduleByDayAndService()` method