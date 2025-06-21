<?php

use App\Infrastructure\Http\Controllers\Administrator\AdminController;
use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\Tickets\TicketController;
use App\Infrastructure\Http\Controllers\Tickets\CommentController;
use App\Infrastructure\Http\Controllers\Tickets\TrackingTimeController;
use App\Infrastructure\Http\Controllers\Tickets\CategoryController;

Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {

    Route::middleware('permission:view tickets')->group(function() {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::post('tickets/{id}/attachments', [TicketController::class, 'addAttachments']);
    });
    

    Route::middleware('permission:create tickets')->group(function() {
        Route::post('/', [TicketController::class, 'store']);
    });
    

    Route::middleware('permission:edit tickets')->group(function() {
        Route::put('/{id}', [TicketController::class, 'update']);
        Route::post('/{id}/attachments', [TicketController::class, 'addAttachments']);
    });
    

    Route::middleware('permission:delete tickets')->group(function() {
        Route::delete('/{id}', [TicketController::class, 'destroy']);
    });
    

    Route::middleware('permission:assign tickets')->group(function() {
        Route::post('/{id}/assign', [TicketController::class, 'assign']);
        Route::post('/{id}/unassign', [TicketController::class, 'unassign']); 

    });
    

    Route::middleware('permission:change statut')->group(function() {
        Route::post('/{id}/status', [TicketController::class, 'changeStatus']);
    });
    

    Route::middleware('permission:resolve tickets')->group(function() {
        Route::post('/{id}/resolve', [TicketController::class, 'resolveTicket']);
    });
    

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

    Route::get('/categories', [CategoryController::class, 'index']);

    // admin routes 
    Route::middleware(['auth:sanctum', 'role:administrator'])->prefix('categories')->group(function () {

        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:administrator'])->prefix('admin')->group(function () {
    Route::post('/users', [AdminController::class, 'store']);
    Route::get('/users', [AdminController::class, 'index']);
    Route::get('/users/{id}', [AdminController::class, 'show']);
    Route::put('/users/{id}', [AdminController::class, 'update']);
    Route::delete('/users/{id}', [AdminController::class, 'destroy']);
    Route::post('/users/{id}/roles-permissions', [AdminController::class, 'assignRolesPermissions']);
    Route::get('/permissions', [AdminController::class, 'getAllPermissions']);
});