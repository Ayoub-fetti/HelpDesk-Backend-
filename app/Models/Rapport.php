<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nom',
        'type',
        'contenu',
        'generer_par_id',
        'date_generation',
    ];
    
    protected $casts = [
        'date_generation' => 'datetime',
    ];
    
    public function generePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generer_par_id');
    }
}