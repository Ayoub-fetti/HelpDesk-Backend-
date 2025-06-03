<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'type',
        'content',
        'genretae_by_id',
        'generation_date',
    ];
    
    protected $casts = [
        'generation_date' => 'datetime',
    ];
    
    public function generetBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'genretae_by_id');
    }
}