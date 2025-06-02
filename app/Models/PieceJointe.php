<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceJointe extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ticket_id',
        'nom_fichier',
        'chemin_fichier',
        'type_mime',
        'taille_fichier',
        'date_upload',
    ];
    
    protected $casts = [
        'date_upload' => 'datetime',
        'taille_fichier' => 'integer',
    ];
    
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}