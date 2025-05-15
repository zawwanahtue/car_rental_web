<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('/user')->group(function () {
    Route::get('/profile', [UserController::class, 'currentUser']);
    Route::get('/logout', [UserController::class, 'logout']);
});