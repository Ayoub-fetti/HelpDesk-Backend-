<?php

namespace App\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'technician' => [
                'id' => $this->technician->id,
                'lastName' => $this->technician->lastName,
                'firstName' => $this->technician->firstName,
                'email' => $this->technician->email,
            ],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'duration' => $this->duration,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_duration' => $this->additional['formatted_duration'] ?? null,
        ];
    }
}