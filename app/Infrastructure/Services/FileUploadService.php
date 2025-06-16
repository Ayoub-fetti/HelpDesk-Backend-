<?php

namespace App\Infrastructure\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadTicketAttachment(UploadedFile $file, int $ticketId): array
    {
        $fileName = $file->getClientOriginalName();
        $filePath = "tickets/{$ticketId}/" . time() . '_' . $fileName;
        
        // Store the file
        Storage::disk('public')->put($filePath, file_get_contents($file));
        
        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'type_mime' => $file->getMimeType(),
            'file_size' => $file->getSize()
        ];
    }
}