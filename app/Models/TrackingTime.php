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
        'technicien_id',
        'date_debut',
        'date_fin',
        'duree',
        'description',
    ];
    
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'duree' => 'float',
    ];
    
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
    
    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}