<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlaygroundController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\FinancialReportController;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Broadcast;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware([CorsMiddleware::class])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    // Add other routes that require CORS handling here
});


    

Route::apiResource('playground', PlaygroundController::class);

// Route::apiResource('playgrounds', PlaygroundController::class);
Route::get('playgrounds/{playground}/schedule/{date}', [ReservationController::class, 'getSchedule']);

Route::middleware('auth:sanctum')->group(function () {

// routes/channels.php

Broadcast::channel('reservations', function ($user) {
    return true; // Adjust as per your authorization logic
});


    Route::get('/my-playgrounds', [PlaygroundController::class, 'myPlaygrounds']);
    Route::get('/get-status-reservations', [ReservationController::class, 'getStatusReservations']);
    Route::post('logout', [AuthController::class, 'logout']);
  
    Route::post('/add-playground', [PlaygroundController::class, 'store'])->middleware(CheckSubscription::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::get('user-reservations', [ReservationController::class,'userReservations']);
    Route::get('financial-reports', [FinancialReportController::class, 'index']);


    Route::get('owner-reservations', [ReservationController::class, 'ownerReservations']);
        // Custom routes for confirming and canceling reservations
    Route::patch('reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);


    // get schedual times :
    // File: routes/api.php


});