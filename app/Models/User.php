<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'departement',
        'type_utilisateur',
        'actif',
        'derniere_connexion',
        'specialisation',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'derniere_connexion' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    public function ticketsAssignes(): HasMany
    {
        return $this->hasMany(Ticket::class, 'technicien_id');
    }

    public function ticketsOuverts(): HasMany
    {
        return $this->hasMany(Ticket::class, 'utilisateur_id');
    }
    
    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class, 'utilisateur_id');
    }
    
    public function suiviTemps(): HasMany
    {
        return $this->hasMany(SuiviTemps::class, 'technicien_id');
    }
    
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'utilisateur_id');
    }
    
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'utilisateur_id');
    }
    
    public function rapports(): HasMany
    {
        return $this->hasMany(Rapport::class, 'generer_par_id');
    }
    
    public function estTechnicien(): bool
    {
        return $this->type_utilisateur === 'technicien';
    }
    
    public function estAdministrateur(): bool
    {
        return $this->type_utilisateur === 'administrateur';
    }
    
    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}