<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\BookingController;
use App\Helpers\Helper;
use App\Http\Controllers\TestingController;

Route::get('/proxy-image', [ImageController::class, 'proxyImage']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Office Location routes
Route::get('/office-locations', [BookingController::class, 'getOfficeLocations']);

// Car routes 
Route::get('/cars', [CarController::class, 'getCars']);

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
        // User routes
        Route::get('/user-list', [UserController::class, 'userList']);
        Route::get('/ban-user/{id}', [UserController::class, 'banAndUnbanUser']);
        Route::post('/admin-register', [UserController::class, 'registerAdmin']);

        // Car routes
        Route::post('/car-create', [CarController::class, 'addCar']);
        Route::post('/car-update/{id}', [CarController::class, 'updateCar']);
        Route::delete('/car-delete/{id}', [CarController::class, 'deleteCar']);

        // Car Type routes
        Route::post('/car-type-create', [CarController::class, 'createCarType']);
        Route::post('/car-type-update/{id}', [CarController::class, 'updateCarType']);
        Route::delete('/car-type-delete/{id}', [CarController::class, 'deleteCarType']);

        // Booking routes
        Route::get('/bookings', [BookingController::class, 'getBookings']);

        // Office Location routes
        Route::post('/office-location-create', [BookingController::class, 'createOfficeLocation']);
        Route::post('/office-location-update/{id}', [BookingController::class, 'updateOfficeLocation']);
        Route::delete('/office-location-delete/{id}', [BookingController::class, 'deleteOfficeLocation']);

        // Owner routes
        Route::get('/owners', [BookingController::class, 'getOwners']);
        Route::post('/owner-create', [BookingController::class, 'createOwner']);
        Route::post('/owner-update/{id}', [BookingController::class, 'updateOwner']);
        Route::delete('/owner-delete/{id}', [BookingController::class, 'deleteOwner']);
    });
    
    // User routes
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/logout', [UserController::class, 'logout']);
    Route::post('/upload&update-profile-image', [UserController::class, 'profileImageRequest']);
    Route::delete('/delete-profile-image', [UserController::class, 'deleteProfileImage']);
    Route::put('/update-profile', [UserController::class, 'updateUser']);

    // Car type routes
    Route::get('/car-types', [CarController::class, 'carTypes']);

    // Booking routes
    Route::get('/bookings/user', [BookingController::class, 'getBookingByUser']);
    Route::post('/booking-create', [BookingController::class, 'createBooking']);

    // Booking type routes
    Route::get('/booking-types', [BookingController::class, 'getBookingType']);

});

Route::get('/mail', [TestingController::class, 'mail']);

//// testing route
