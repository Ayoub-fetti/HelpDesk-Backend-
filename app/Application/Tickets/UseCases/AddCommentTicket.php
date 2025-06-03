<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NewCommentDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUser;

class AddCommentTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(TicketRepositoryInterface $ticketRepository,TicketService $ticketService) {
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

        // Appeler le service de domaine pour ajouter le commentaire
        $this->ticketService->addComment(
            $ticket,
            $dto->content,
            $user,
            $dto->isPrivate ?? false
        );
        
        // Sauvegarder les modifications et récupérer l'ID du commentaire
        $commentId = $this->ticketRepository->update($ticket);
        
        return (int) $commentId;
    }
}