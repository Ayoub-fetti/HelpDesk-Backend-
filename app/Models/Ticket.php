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
        'title',
        'description',
        'user_id',
        'technician_id',
        'category_id',
        'priority',
        'statut',
        'resolution_date',
        'solution',
        'time_pass_total',
    ];
    
    protected $casts = [
        'resolution_date' => 'datetime',
        'time_pass_total' => 'float',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
    
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
    
    public function trackingTimes(): HasMany
    {
        return $this->hasMany(TrackingTime::class);
    }
    
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
    
    public function scopeOpens($query)
    {
        return $query->whereNotIn('statut', ['resolved', 'closed']);
    }
    
    public function scopeCloses($query)
    {
        return $query->whereIn('statut', ['resolved', 'closed']);
    }
}