<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    
    protected $table = 'categories';
    
    protected $fillable = [
        'nom',
        'description',
        'actif',
    ];
    
    protected $casts = [
        'actif' => 'boolean',
    ];
    
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'categorie_id');
    }
}