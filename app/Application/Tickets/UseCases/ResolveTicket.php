<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\ResolveTicketDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUser;

class ResolveTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        TicketService $ticketService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    public function execute(ResolveTicketDTO $dto): void
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

        // Appeler le service de domaine pour résoudre le ticket
        $this->ticketService->resolveTicket($ticket, $dto->solution, $user);
        
        // Ajouter un commentaire si fourni
        if (!empty($dto->comment)) {
            $this->ticketService->addComment(
                $ticket,
                $dto->comment,
                $user
            );
        }
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
    }
}