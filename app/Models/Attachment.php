<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ticket_id',
        'file_name',
        'file_path',
        'type_mime',
        'file_size',
        'upload_date',
    ];
    
    protected $casts = [
        'upload_date' => 'datetime',
        'file_size' => 'integer',
    ];
    
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}