<?php

namespace App\Infrastructure\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TrackingTime;
use Illuminate\Support\Facades\Auth;
use App\Infrastructure\Http\Requests\Tickets\CreateTrackingTimeRequest;
use App\Infrastructure\Http\Resources\TrackingTimeResource;

class TrackingTimeController extends Controller
{
    public function index(int $ticketId): JsonResponse
    {
        $trackingTimes = TrackingTime::where('ticket_id', $ticketId)
            ->with('technician')
            ->orderBy('start_date', 'desc')
            ->get();
            
        return response()->json(TrackingTimeResource::collection($trackingTimes));
    }
    
    public function startChrono(int $ticketId, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Check if there's already an active tracking session for this user and ticket
        $activeTracking = TrackingTime::where('ticket_id', $ticketId)
            ->where('technician_id', $user->id)
            ->whereNull('end_date')
            ->first();
        
        if ($activeTracking) {
            return response()->json([
                'message' => 'You already have an active timing session for this ticket'
            ], 422);
        }
        
        $trackingTime = new TrackingTime([
            'ticket_id' => $ticketId,
            'technician_id' => $user->id,
            'start_date' => now(),
            'description' => $request->input('description', 'Working on ticket')
        ]);
        
        $trackingTime->save();
        
        return response()->json(new TrackingTimeResource($trackingTime), 201);
    }

    public function stopChrono(int $ticketId): JsonResponse
    {
        $user = Auth::user();
        
        // Find active tracking session
        $activeTracking = TrackingTime::where('ticket_id', $ticketId)
            ->where('technician_id', $user->id)
            ->whereNull('end_date')
            ->first();
        
        if (!$activeTracking) {
            return response()->json([
                'message' => 'No active timing session found'
            ], 404);
        }
        
        // Set end time and calculate duration
        $now = now();
        $activeTracking->end_date = $now;
        
        // Calculate duration in hours
        $startTime = new \DateTime($activeTracking->start_date);
        $endTime = new \DateTime($now);
        $interval = $startTime->diff($endTime);
        $duration = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
        
        $activeTracking->duration = round($duration, 2);
        $activeTracking->save();
        
        return response()->json(new TrackingTimeResource($activeTracking));
    }
}