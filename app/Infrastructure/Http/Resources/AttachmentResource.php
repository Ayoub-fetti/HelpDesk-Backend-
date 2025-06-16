<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'ticket_id' => $this->getTicketId(),
            'file_name' => $this->getFileName(),
            'file_path' => $this->getFilePath(),
            'type_mime' => $this->getTypeMime(),
            'file_size' => $this->getFileSize(),
            'url' => asset('storage/' . $this->getFilePath()),
            'upload_date' => $this->getUploadDate()
        ];
    }
}