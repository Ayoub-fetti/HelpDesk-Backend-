<?php

use App\Infrastructure\Http\Controllers\Tickets\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationsController::class, 'index']);
    Route::post('/{id}/read', [NotificationsController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationsController::class, 'markAllAsRead']);
    Route::get('/unread-count', [NotificationsController::class, 'unreadCount']);

});