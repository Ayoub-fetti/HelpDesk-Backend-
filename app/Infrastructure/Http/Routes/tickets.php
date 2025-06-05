<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\Tickets\TicketController;
use App\Infrastructure\Http\Controllers\Tickets\CommentController;
use App\Infrastructure\Http\Controllers\Tickets\TrackingTimeController;

Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {
    // Ticket routes V
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::get('/{id}', [TicketController::class, 'show']);
    Route::put('/{id}', [TicketController::class, 'update']);
    Route::delete('/{id}', [TicketController::class, 'destroy']);
    
    // Specific ticket actions V
    Route::post('/{id}/assign', [TicketController::class, 'assign']);
    Route::post('/{id}/status', [TicketController::class, 'changeStatus']);
    Route::post('/{id}/resolve', [TicketController::class, 'resolveTicket']);
    Route::post('/{id}/close', [TicketController::class, 'close']);
    
    // Comments V
    Route::get('/{id}/comments', [CommentController::class, 'index']);
    Route::post('/{id}/comments', [CommentController::class, 'store']);
    
    // Tracking Time 
    Route::get('/{id}/tracking', [TrackingTimeController::class, 'index']);
    Route::post('/{id}/tracking', [TrackingTimeController::class, 'store']);
});