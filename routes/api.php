<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BookingController;
use App\Helpers\Helper;

Route::get('/proxy-image', [ImageController::class, 'proxyImage']);

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/locations', [LocationController::class, 'getAllLocations']);
Route::get('/car-types', [CarController::class, 'carTypes']);

Route::middleware('auth:sanctum')->group(function () 
{
    // User routes
    Route::middleware('user_type:1')->prefix('/user')->group(function (){
        
    });

    // Staff routes
    Route::middleware('user_type:2')->prefix('/staff')->group(function (){
        // Route::get('/users', [UserController::class, 'getAllUsers']);
    });
    
    // Admin routes
    Route::middleware('user_type:3')->prefix('/admin')->group(function (){
        // Route::get('/users', [UserController::class, 'getAllUsers']);
        Route::get('/user-list', [UserController::class, 'userList']);
        Route::patch('/ban-user/{id}', [UserController::class, 'banAndUnbanUser']);

        // Car routes
        Route::post('/car-create', [CarController::class, 'addCar']);
        Route::patch('/car-update/{id}', [CarController::class, 'updateCar']);
        Route::delete('/car-delete/{id}', [CarController::class, 'deleteCar']);

        // Car Type routes
        Route::post('/car-type-create', [CarController::class, 'createCarType']);
        Route::post('/car-type-update/{id}', [CarController::class, 'updateCarType']);
        Route::delete('/car-type-delete/{id}', [CarController::class, 'deleteCarType']);

        // Booking Type routes
        Route::post('/booking-type-create', [BookingController::class, 'createBookingType']);
        Route::put('/booking-type-update/{id}', [BookingController::class, 'updateBookingType']);
        Route::delete('/booking-type-delete/{id}', [BookingController::class, 'deleteBookingType']);

        // Location Type routes
        Route::post('/location-type-create', [LocationController::class, 'createLocationType']);
        Route::put('/location-type-update/{id}', [LocationController::class, 'updateLocationType']);
        Route::delete('/location-type-delete/{id}', [LocationController::class, 'deleteLocationType']);
    });

    // Car routes 
    Route::get('/cars', [CarController::class, 'getCars']);

    // Booking routes
    Route::get('/bookings', [BookingController::class, 'getBookings']);
    Route::get('/bookings/user/{id}', [BookingController::class, 'getBookingByUser']);
    Route::post('/booking-create', [BookingController::class, 'createBooking']);
    Route::patch('/booking-cancel/{id}', [BookingController::class, 'cancelBooking']);
    Route::patch('/booking-complete/{id}', [BookingController::class, 'completeBooking']);

    // Booking type routes
    Route::get('/booking-types', [BookingController::class, 'getBookingType']);
    
    // Location routes
    Route::get('/locations', [LocationController::class, 'getAllLocations']);
    Route::post('/location-create', [LocationController::class, 'createLocation']);
    Route::put('/location-update/{id}', [LocationController::class, 'updateLocation']);
    Route::delete('/location-delete/{id}', [LocationController::class, 'deleteLocation']);

    // Location Type routes
    Route::get('/location-types', [LocationController::class, 'getAllLocationTypes']);

    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/logout', [UserController::class, 'logout']);
    Route::post('/upload&update-profile-image', [UserController::class, 'profileImageRequest']);
    Route::delete('/delete-profile-image', [UserController::class, 'deleteProfileImage']);
    Route::put('/update-profile', [UserController::class, 'updateUser']);

    // Route::post('/email/verification-notification', [UserController::class, 'sendVerificationEmail']);
    // Route::get('/verify-email/{id}/{hash}', [UserController::class, 'verify'])->name('verification.verify');
});

        Route::post('/car-create', [CarController::class, 'addCar']);
        Route::get('/list-file', [UserController::class, 'listFiles']);

//// testing route
