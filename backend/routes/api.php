<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes pour les véhicules (publiques)
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
Route::post('/vehicles/{id}/check-availability', [VehicleController::class, 'checkAvailability']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Locations utilisateur
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::get('/rentals/{id}', [RentalController::class, 'show']);

    // Routes admin
    Route::middleware('admin')->group(function () {
        Route::get('/admin/stats', [AdminController::class, 'stats']);
        
        // Gestion des véhicules
        Route::post('/admin/vehicles', [AdminController::class, 'vehicleStore']);
        Route::put('/admin/vehicles/{id}', [AdminController::class, 'vehicleUpdate']);
        Route::delete('/admin/vehicles/{id}', [AdminController::class, 'vehicleDestroy']);
        
        // Gestion des réservations
        Route::get('/admin/rentals', [AdminController::class, 'rentalIndex']);
        Route::put('/admin/rentals/{id}', [AdminController::class, 'rentalUpdate']);
    });
});