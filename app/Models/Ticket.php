<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'titre',
        'description',
        'utilisateur_id',
        'technicien_id',
        'categorie_id',
        'priorite',
        'statut',
        'date_resolution',
        'solution',
        'temps_passe_total',
    ];
    
    protected $casts = [
        'date_resolution' => 'datetime',
        'temps_passe_total' => 'float',
    ];
    
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
    
    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
    
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }
    
    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class);
    }
    
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointe::class);
    }
    
    public function suiviTemps(): HasMany
    {
        return $this->hasMany(SuiviTemps::class);
    }
    
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
    
    public function scopeOuverts($query)
    {
        return $query->whereNotIn('statut', ['résolu', 'fermé']);
    }
    
    public function scopeFermes($query)
    {
        return $query->whereIn('statut', ['résolu', 'fermé']);
    }
}