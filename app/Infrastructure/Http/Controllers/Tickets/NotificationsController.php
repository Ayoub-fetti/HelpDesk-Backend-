<?php

namespace App\Infrastructure\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json($notifications);
    }
    
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();
        
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json(['message' => 'Notification marked as read']);
    }
    
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'All notifications marked as read']);
    }
    
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications->count();
        
        return response()->json([
            'unread_count' => $count
        ]);
    }
}