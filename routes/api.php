<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/customer-analytics', [DashboardController::class, 'customerAnalytics']);
    Route::get('/dashboard/revenue-metrics', [DashboardController::class, 'revenueMetrics']);
    Route::get('/dashboard/unit-status', [DashboardController::class, 'unitStatus']);
    Route::get('/notifications', [DashboardController::class, 'notifications']);

    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/rooms/availability', [ReservationController::class, 'availability']);

    Route::get('/rooms/metrics', [RoomController::class, 'metrics']);
    Route::get('/rooms/filters', [RoomController::class, 'filters']);
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{room}', [RoomController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    Route::get('/rooms/export', [RoomController::class, 'export']);
    Route::get('/rooms/availability/list', [RoomController::class, 'availability']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);

    Route::get('/search', [SearchController::class, 'autocomplete']);
});
