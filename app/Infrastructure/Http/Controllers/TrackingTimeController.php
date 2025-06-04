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
    
    public function store(int $ticketId, CreateTrackingTimeRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        $trackingTime = new TrackingTime([
            'ticket_id' => $ticketId,
            'technician_id' => $user->id,
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'duration' => $request['duration'],
            'description' => $request['description']
        ]);
        
        $trackingTime->save();
        
        return response()->json(new TrackingTimeResource($trackingTime), 201);
    }
}