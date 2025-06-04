<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingTime extends Model
{
    use HasFactory;
    
    protected $table = 'tracking_times';
    
    protected $fillable = [
        'ticket_id',
        'technician_id',
        'start_date',
        'end_date',
        'duration',
        'description',
    ];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'duration' => 'float',
    ];
    
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
    
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}