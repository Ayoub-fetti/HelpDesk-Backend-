<?php

namespace App\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'ticket_id' => $this->getTicketId(),
            'author_id' => $this->getAuthorId(),
            'content' => $this->getContent(),
            'is_private' => $this->isPrivate(),
            'created_at' => $this->getCreationDate()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getModificationDate() ? 
                $this->getModificationDate()->format('Y-m-d H:i:s') : null,
        ];
    }
}