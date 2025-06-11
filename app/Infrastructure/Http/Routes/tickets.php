<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\Tickets\TicketController;
use App\Infrastructure\Http\Controllers\Tickets\CommentController;
use App\Infrastructure\Http\Controllers\Tickets\TrackingTimeController;

Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {
    // Routes accessible with 'view tickets' permission
    Route::middleware('permission:view tickets')->group(function() {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'show']);
    });
    
    // Routes requiring 'create tickets' permission
    Route::middleware('permission:create tickets')->group(function() {
        Route::post('/', [TicketController::class, 'store']);
    });
    
    // Routes requiring 'edit tickets' permission
    Route::middleware('permission:edit tickets')->group(function() {
        Route::put('/{id}', [TicketController::class, 'update']);
    });
    
    // Routes requiring 'delete tickets' permission (admin only)
    Route::middleware('permission:delete tickets')->group(function() {
        Route::delete('/{id}', [TicketController::class, 'destroy']);
    });
    
    // Routes requiring 'assign tickets' permission (supervisor and admin)
    Route::middleware('permission:assign tickets')->group(function() {
        Route::post('/{id}/assign', [TicketController::class, 'assign']);
    });
    
    // Routes requiring 'change status' permission
    Route::middleware('permission:change statut')->group(function() {
        Route::post('/{id}/status', [TicketController::class, 'changeStatus']);
    });
    
    // Routes requiring 'resolve tickets' permission
    Route::middleware('permission:resolve tickets')->group(function() {
        Route::post('/{id}/resolve', [TicketController::class, 'resolveTicket']);
    });
    
    // Routes requiring 'close tickets' permission
    Route::middleware('permission:close tickets')->group(function() {
        Route::post('/{id}/close', [TicketController::class, 'close']);
    });
    
    // Comments routes
    Route::middleware('permission:view comments')->group(function() {
        Route::get('/{id}/comments', [CommentController::class, 'index']);
    });
    
    Route::middleware('permission:add comments')->group(function() {
        Route::post('/{id}/comments', [CommentController::class, 'store']);
    });
    
    // Time tracking routes
    Route::middleware('permission:view time tracking')->group(function() {
        Route::get('/{id}/tracking', [TrackingTimeController::class, 'index']);
    });
    
    Route::middleware('permission:track time')->group(function() {
        Route::post('/{id}/tracking-time/start', [TrackingTimeController::class, 'startChrono']);
        Route::post('/{id}/tracking-time/stop', [TrackingTimeController::class, 'stopChrono']);
    });
});