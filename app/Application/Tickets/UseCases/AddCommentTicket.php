<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NewCommentDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use App\Models\User;
use App\Notifications\CommentAdded;
use Illuminate\Support\Facades\Notification;

class AddCommentTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(TicketRepositoryInterface $ticketRepository, TicketService $ticketService) {
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    public function execute(NewCommentDTO $dto): int
    {
        // Récupérer le ticket depuis le repository
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        
        if (!$ticket) {
            throw new \InvalidArgumentException("Le ticket #{$dto->ticketId} n'existe pas");
        }

        // Créer l'identité de l'utilisateur effectuant l'action
        $user = new IdentiteUser(
            $dto->userId,
            $dto->userLastName,
            $dto->userFirstName,
            $dto->userEmail,  
            $dto->userType
        );

        // Create comment through domain service
        $this->ticketService->addComment(
            $ticket,
            $dto->content,
            $user,
            $dto->isPrivate ?? false
        );
        
        // Save the ticket with the new comment
        $this->ticketRepository->update($ticket);
        
        // Get the newly added comment (the last one in the array)
        $comments = $ticket->getComments();
        $newComment = end($comments);
        
        // Determine who should receive this notification
        $usersToNotify = [];
        
        // If the comment is not private, notify the ticket creator
        if (!$dto->isPrivate) {
            $creatorUser = User::find($ticket->getUser()->getId());
            if ($creatorUser && $creatorUser->id !== $dto->userId) {
                $usersToNotify[] = $creatorUser;
            }
        }
        
        // Always notify the assigned technician (if any)
        if ($ticket->getTechnician()) {
            $technicianUser = User::find($ticket->getTechnician()->getId());
            if ($technicianUser && $technicianUser->id !== $dto->userId) {
                $usersToNotify[] = $technicianUser;
            }
        }
        
        // If the comment is private, notify supervisors and administrators
        if ($dto->isPrivate) {
            $staffUsers = User::whereIn('user_type', ['administrator', 'supervisor'])
                ->where('id', '!=', $dto->userId)
                ->get();
            
            $usersToNotify = array_merge($usersToNotify, $staffUsers->all());
        }
        
        // Send notifications
        Notification::send($usersToNotify, new CommentAdded($ticket, $newComment, $user));
        
        return $newComment->getId();
    }
}