<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\CloseTicketDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use App\Models\User;
use App\Notifications\TicketClosed;
use Illuminate\Support\Facades\Notification;

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
        
        // Determine who to notify
        $usersToNotify = [];
        
        // Always notify the ticket creator if they're not the one closing the ticket
        $creatorUser = User::find($ticket->getUser()->getId());
        if ($creatorUser && $creatorUser->id !== $dto->userId) {
            $usersToNotify[] = $creatorUser;
        }
        
        // Notify the assigned technician if there is one and they're not the one closing the ticket
        if ($ticket->getTechnician() && $ticket->getTechnician()->getId() !== $dto->userId) {
            $technicianUser = User::find($ticket->getTechnician()->getId());
            if ($technicianUser) {
                $usersToNotify[] = $technicianUser;
            }
        }
        
        // Notify supervisors except the one closing the ticket
        $supervisors = User::where('user_type', 'supervisor')
            ->where('id', '!=', $dto->userId)
            ->get();
        
        $usersToNotify = array_merge($usersToNotify, $supervisors->all());
        
        // Send notifications
        Notification::send($usersToNotify, new TicketClosed($ticket, $user));
    }
}