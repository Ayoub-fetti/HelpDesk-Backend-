<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'lastName',
        'firstName',
        'email',
        'password',
        'departement',
        'user_type',
        'active',
        'last_connection',
        'specialization',
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
        return $this->hasMany(Ticket::class, 'technician_id');
    }

    public function ticketsOpen(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }
    
    public function trackingTimes(): HasMany
    {
        return $this->hasMany(TrackingTime::class, 'technician_id');
    }
    
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }
    
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'genretae_by_id');
    }
    
    public function isTechnician(): bool
    {
        return $this->user_type === 'technician';
    }
    
    public function isAdministrator(): bool
    {
        return $this->user_type === 'administrator';
    }
    
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}