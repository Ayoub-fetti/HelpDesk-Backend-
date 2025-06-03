<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\CloseTicketDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;

class CloseTicket
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

    public function execute(CloseTicketDTO $dto): void
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

        // Si une solution est fournie, résoudre d'abord le ticket
        if ($ticket->getStatut() !== StatutTicket::RESOLVED && !empty($dto->solution)) {
            $this->ticketService->resolveTicket($ticket, $dto->solution, $user);
        }

        // Appeler le service de domaine pour changer le statut à FERMÉ
        $this->ticketService->changeStatut($ticket, StatutTicket::CLOSED, $user);
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
    }
}