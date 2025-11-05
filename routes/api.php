<?php

use App\Http\Controllers\Api\WhatsappAppointmentDetailsController;
use App\Http\Controllers\Api\WhatsappCreateAppointmentController;
use App\Http\Controllers\Api\WhatsappDeleteAppointmentController;
use App\Http\Controllers\Api\WhatsappServiceController;
use App\Http\Controllers\Api\WhatsappServiceDateAvailabilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/whatsapp')->group(function () {
    Route::get('/services', WhatsappServiceController::class)
        ->name('api.whatsapp.services');
    
    Route::post('/service-date-availability', WhatsappServiceDateAvailabilityController::class)
        ->name('api.whatsapp.service-date-availability');
    
    Route::post('/create-appointment', WhatsappCreateAppointmentController::class)
        ->name('api.whatsapp.create-appointment');
    
    Route::get('/appointment-details', WhatsappAppointmentDetailsController::class)
        ->name('api.whatsapp.appointment-details');
    
    Route::delete('/delete-appointment', WhatsappDeleteAppointmentController::class)
        ->name('api.whatsapp.delete-appointment');
});

