<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Entities\Comment;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PriorityTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use App\Domains\Tickets\Entities\Attachment;
use App\Models\Ticket as TicketModel;
use App\Models\Comment as CommentModel;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function findById(int $id): ?Ticket
    {
        $ticketModel = TicketModel::with(['user', 'technician', 'comments', 'attachments'])->find($id);
        
        if (!$ticketModel) {
            return null;
        }
        
        $attachments = [];
        foreach ($ticketModel->attachments as $attachmentModel) {
            $attachments[] = new Attachment(
                $attachmentModel->id,
                $attachmentModel->ticket_id,
                $attachmentModel->file_name,
                $attachmentModel->file_path,
                $attachmentModel->type_mime,
                $attachmentModel->file_size,
                $attachmentModel->created_at
            );
        }
        
        // Create and return the Ticket entity with attachments
        $ticket = new Ticket(
            $ticketModel->id,
            $ticketModel->title,
            $ticketModel->description,
            StatutTicket::fromString($ticketModel->statut),
            PriorityTicket::fromString($ticketModel->priority),
            new IdentiteUser(
                $ticketModel->user->id,
                $ticketModel->user->lastName,
                $ticketModel->user->firstName,
                $ticketModel->user->email,
                $ticketModel->user->userType ?? 'final_user'
            ),
            $ticketModel->category_id,
            new DateTime($ticketModel->created_at)
        );
        
        if ($ticketModel->technician) {
            $ticket->assignTechnician(new IdentiteUser(
                $ticketModel->technician->id,
                $ticketModel->technician->lastName,
                $ticketModel->technician->firstName,
                $ticketModel->technician->email,
                $ticketModel->technician->userType ?? 'technician'
            ));
        }
        
        if ($ticketModel->resolution_date) {
            $ticket->setResolutionDate(new DateTime($ticketModel->resolution_date));
        }
        
        if ($ticketModel->solution) {
            $ticket->setSolution($ticketModel->solution);
        }
        
        if ($ticketModel->time_pass_total > 0) {
            $ticket->setTimePass($ticketModel->time_pass_total);
        }
        
        // Charger les commentaires si disponibles
        if ($ticketModel->relationLoaded('comments')) {
            foreach ($ticketModel->comments as $commentModel) {
                $ticket->addComment($this->mapCommentModelToEntity($commentModel));
            }
        }
        
        // Add each attachment to the ticket
        foreach ($attachments as $attachment) {
            $ticket->addAttachment($attachment);
        }
        
        return $ticket;
    }
    
    public function findAll(): array
    {
        $ticketModels = TicketModel::with(['user', 'technician'])->get();
        
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByStatut(StatutTicket $statut): array
    {
        $ticketModels = TicketModel::where('statut', $statut->toString())
            ->with(['user', 'technician'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByUser(int $userId): array
    {
        $ticketModels = TicketModel::where('user_id', $userId)
            ->with(['user', 'technician'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByTechnician(int $technicianId): array
    {
        $ticketModels = TicketModel::where('technician_id', $technicianId)
            ->with(['user', 'technician'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByCategory(int $categoryId): array
    {
        $ticketModels = TicketModel::where('category_id', $categoryId)
            ->with(['user', 'technician'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function save(Ticket $ticket): int
    {
        $ticketData = $this->prepareTicketData($ticket);
        
        $ticketModel = TicketModel::create($ticketData);
        
        // Enregistrer les commentaires si présents
        foreach ($ticket->getComments() as $comment) {
            $this->addComment($comment, $ticketModel->id);
        }
        
        return $ticketModel->id;
    }
    
    public function update(Ticket $ticket): void
    {
        $ticketData = $this->prepareTicketData($ticket);
        
        TicketModel::where('id', $ticket->getId())->update($ticketData);
        
        // Si de nouveaux commentaires ont été ajoutés, les enregistrer
        $existingComments = CommentModel::where('ticket_id', $ticket->getId())->pluck('id')->toArray();
        
        foreach ($ticket->getComments() as $comment) {
            if ($comment->getId() === 0 || !in_array($comment->getId(), $existingComments)) {
                $this->addComment($comment, $ticket->getId());
            }
        }
    }
    
    public function addComment(Comment $comment, ?int $ticketId = null): int
    {
        $commentModel = CommentModel::create([
            'ticket_id' => $ticketId ?? $comment->getTicketId(),
            'user_id' => $comment->getAuthorId(),
            'content' => $comment->getContent(),
            'private' => $comment->isPrivate(),
        ]);
        
        return $commentModel->id;
    }
    
    public function findCommentsByTicketId(int $ticketId): array
    {
        $commentModels = CommentModel::where('ticket_id', $ticketId)
            ->with('user')
            ->get();
            
        return $commentModels->map(function($commentModel) {
            return $this->mapCommentModelToEntity($commentModel);
        })->toArray();
    }
    
    public function search(array $criteria): array
    {
        $query = TicketModel::query();
        
        if (isset($criteria['statut'])) {
            $query->where('statut', $criteria['statut']);
        }
        
        if (isset($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }
        
        if (isset($criteria['category_id'])) {
            $query->where('category_id', $criteria['category_id']);
        }
        
        if (isset($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }
        
        if (isset($criteria['technician_id'])) {
            $query->where('technician_id', $criteria['technician_id']);
        }
        
        if (isset($criteria['text'])) {
            $query->where(function($q) use ($criteria) {
                $q->where('title', 'like', '%' . $criteria['text'] . '%')
                  ->orWhere('description', 'like', '%' . $criteria['text'] . '%');
            });
        }
        
        if (isset($criteria['start_date']) && isset($criteria['end_date'])) {
            $query->whereBetween('created_at', [$criteria['start_date'], $criteria['end_date']]);
        }
        
        $ticketModels = $query->with(['user', 'technician'])->get();
        
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function getStatistics(array $filtres = []): array
    {
        $query = TicketModel::query();
        
        // Appliquer des filtres si nécessaire
        if (!empty($filtres)) {
            if (isset($filtres['start_date']) && isset($filtres['end_date'])) {
                $query->whereBetween('created_at', [$filtres['start_date'], $filtres['end_date']]);
            }
            
            if (isset($filtres['technician_id'])) {
                $query->where('technician_id', $filtres['technician_id']);
            }
        }
        
        $totalTickets = $query->count();
        $ticketsByStatut = $query->select('statut', DB::raw('count(*) as total'))
                                  ->groupBy('statut')
                                  ->pluck('total', 'statut')
                                  ->toArray();
                                  
        $ticketsByPriorite = $query->select('priority', DB::raw('count(*) as total'))
                                   ->groupBy('priority')
                                   ->pluck('total', 'priority')
                                   ->toArray();
                                   
        $timeResolutionAverage = $query->whereNotNull('resolution_date')
                                      ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolution_date)'));
        
        return [
            'total_tickets' => $totalTickets,
            'by_statut' => $ticketsByStatut,
            'by_priority' => $ticketsByPriorite,
            'time_resolution_average' => $timeResolutionAverage,
        ];
    }
    
    public function delete(int $ticketId): bool
    {
        return TicketModel::destroy($ticketId) > 0;
    }
    public function addAttachment(Attachment $attachment): int
    {
        $attachmentModel = new \App\Models\Attachment([
            'ticket_id' => $attachment->getTicketId(),
            'file_name' => $attachment->getFileName(),
            'file_path' => $attachment->getFilePath(),
            'type_mime' => $attachment->getTypeMime(),
            'file_size' => $attachment->getFileSize(),
            'upload_date' => $attachment->getUploadDate()
        ]);
        
        $attachmentModel->save();
        
        return $attachmentModel->id;
    }
    
    /**
     * Get all attachments for a ticket
     */
    public function getAttachmentsByTicketId(int $ticketId): array
    {
        $attachmentModels = \App\Models\Attachment::where('ticket_id', $ticketId)->get();
        $attachments = [];
        
        foreach ($attachmentModels as $model) {
            $attachments[] = new \App\Domains\Tickets\Entities\Attachment(
                $model->id,
                $model->ticket_id,
                $model->file_name,
                $model->file_path,
                $model->type_mime,
                $model->file_size,
                new \DateTime($model->created_at)
            );
        }
        
        return $attachments;
    }

    /**
     * Remove an attachment
     */
    public function removeAttachment(int $attachmentId): bool
    {
        return \App\Models\Attachment::destroy($attachmentId) > 0;
    }
    
    
    // Convertit un modèle Eloquent de ticket en entité du domaine
    
    private function mapTicketModelToEntity(TicketModel $ticketModel): Ticket
    {
        $user = new IdentiteUser(
            $ticketModel->user->id,
            $ticketModel->user->lastName,
            $ticketModel->user->firstName,
            $ticketModel->user->email,
            $ticketModel->user->userType ?? 'final_user'
        );
        
        $technician = null;
        if ($ticketModel->technician) {
            $technician = new IdentiteUser(
                $ticketModel->technician->id,
                $ticketModel->technician->lastName,
                $ticketModel->technician->firstName,
                $ticketModel->technician->email,
                $ticketModel->technician->userType ?? 'technician'
            );
        }
        
        $ticket = new Ticket(
            $ticketModel->id,
            $ticketModel->title,
            $ticketModel->description,
            StatutTicket::fromString($ticketModel->statut),
            PriorityTicket::fromString($ticketModel->priority),
            $user,
            $ticketModel->category_id,
            new DateTime($ticketModel->created_at)
        );
        
        if ($ticketModel->technician) {
        $technician = new IdentiteUser(
            $ticketModel->technician->id,
            $ticketModel->technician->lastName,
            $ticketModel->technician->firstName,
            $ticketModel->technician->email,
            $ticketModel->technician->userType ?? 'technician'
        );
        $ticket->assignTechnician($technician);
    }
        
        if ($technician) {
            $ticket->assignTechnician($technician);
        }
        
        if ($ticketModel->resolution_date) {
            $ticket->setResolutionDate(new DateTime($ticketModel->resolution_date));
        }
        
        if ($ticketModel->solution) {
            $ticket->setSolution($ticketModel->solution);
        }
        
        if ($ticketModel->time_pass_total > 0) {
            $ticket->setTimePass($ticketModel->time_pass_total);
        }
        
        // Charger les commentaires si disponibles
        if ($ticketModel->relationLoaded('comments')) {
            foreach ($ticketModel->comments as $commentModel) {
                $ticket->addComment($this->mapCommentModelToEntity($commentModel));
            }
        }
        
        return $ticket;
    }
    
    
    // Convertit un modèle Eloquent de commentaire en entité du domaine
     
    private function mapCommentModelToEntity(CommentModel $commentModel): Comment
    {
        $comment = new Comment(
            $commentModel->id,
            $commentModel->ticket_id,
            $commentModel->user_id,
            $commentModel->content,
            (bool) $commentModel->private,
            new DateTime($commentModel->created_at)
        );
        
        if ($commentModel->updated_at && $commentModel->updated_at !== $commentModel->created_at) {
            $comment->setModificationDate(new DateTime($commentModel->updated_at));
        }
        
        return $comment;
    }
    
    
    // Prépare les données pour l'enregistrement ou la mise à jour d'un ticket
     
    private function prepareTicketData(Ticket $ticket): array
    {
        $data = [
            'title' => $ticket->getTitle(),
            'description' => $ticket->getDescription(),
            'statut' => $ticket->getStatut()->toString(),
            'priority' => $ticket->getPriority()->toString(),
            'user_id' => $ticket->getUser()->getId(),
            'category_id' => $ticket->getCategoryId(),
            'solution' => $ticket->getSolution(),
            'time_pass_total' => $ticket->getTimePass(),
        ];
        
        if ($ticket->getTechnician()) {
            $data['technician_id'] = $ticket->getTechnician()->getId();
        }
        
        if ($ticket->getResolutionDate()) {
            $data['resolution_date'] = $ticket->getResolutionDate()->format('Y-m-d H:i:s');
        }
        
        return $data;
    }
}