<?php

namespace App\Infrastructure\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $category = Category::find($this->getCategoryId());


        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'statut' => $this->getStatut()->toString(),
            'priority' => $this->getPriority()->toString(),
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ] : null,
            'user' => [
                'id' => $this->getUser()->getId(),
                'lastName' => $this->getUser()->getLastName(),
                'firstName' => $this->getUser()->getFirstName(),
                'email' => $this->getUser()->getEmail(),
                'type' => $this->getUser()->getUserType(),
            ],
            'technician' => $this->getTechnician() ? [
                'id' => $this->getTechnician()->getId(),
                'lastName' => $this->getTechnician()->getLastName(),
                'firstName' => $this->getTechnician()->getFirstName(),
                'email' => $this->getTechnician()->getEmail(),
                'type' => $this->getTechnician()->getUserType(),
            ] : null,
            'created_at' => $this->getCreationDate()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdationDate()->format('Y-m-d H:i:s'),
            'resolution_date' => $this->getResolutionDate() ? 
                $this->getResolutionDate()->format('Y-m-d H:i:s') : null,
            'solution' => $this->getSolution(),
            'time_pass_total' => $this->getTimePass(),
            'comments' => CommentResource::collection($this->getComments()),
            'attachments' => $this->formatAttachments(),
        ];
    }
    protected function formatAttachments(): array
    {
        $attachments = $this->getAttachments();
        $formattedAttachments = [];
        
        foreach ($attachments as $attachment) {
            $formattedAttachments[] = [
                'id' => $attachment->getId(),
                'file_name' => $attachment->getFileName(),
                'file_path' => $attachment->getFilePath(),
                'type_mime' => $attachment->getTypeMime(),
                'file_size' => $attachment->getFileSize(),
                'url' => asset('storage/' . $attachment->getFilePath()),
                'upload_date' => $attachment->getUploadDate()
            ];
        }
        
        return $formattedAttachments;
    }
}