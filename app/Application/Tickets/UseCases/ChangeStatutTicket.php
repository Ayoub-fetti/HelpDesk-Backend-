<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\ChangeStatutDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use App\Models\User;
use App\Notifications\TicketStatusChanged;
use Illuminate\Support\Facades\Notification;

class ChangeStatutTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(TicketRepositoryInterface $ticketRepository, TicketService $ticketService) {
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    public function execute(ChangeStatutDTO $dto): void
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

        // Remember old status for notification purposes
        $oldStatus = $ticket->getStatut();

        // Convertir la chaîne de statut en objet StatutTicket
        $newStatut = StatutTicket::fromString($dto->newStatut);
        
        // Appeler le service de domaine pour changer le statut
        $this->ticketService->changeStatut($ticket, $newStatut, $user);
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
        
        // Determine who to notify
        $usersToNotify = [];
        
        // Always notify the ticket creator if they're not the one changing the status
        $creatorUser = User::find($ticket->getUser()->getId());
        if ($creatorUser && $creatorUser->id !== $dto->userId) {
            $usersToNotify[] = $creatorUser;
        }
        
        // Notify the assigned technician if there is one and they're not the one changing the status
        if ($ticket->getTechnician() && $ticket->getTechnician()->getId() !== $dto->userId) {
            $technicianUser = User::find($ticket->getTechnician()->getId());
            if ($technicianUser) {
                $usersToNotify[] = $technicianUser;
            }
        }
        
        // For certain status changes, notify supervisors
        if (in_array($newStatut->toString(), ['resolved', 'closed', 'reopen'])) {
            $supervisors = User::where('user_type', 'supervisor')
                ->where('id', '!=', $dto->userId)
                ->get();
            
            $usersToNotify = array_merge($usersToNotify, $supervisors->all());
        }
        
        // Send notifications
        // Notification::send($usersToNotify, new TicketStatusChanged($ticket, $oldStatus, $newStatut, $user));
    }
}