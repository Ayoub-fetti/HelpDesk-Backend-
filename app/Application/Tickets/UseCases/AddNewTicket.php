<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NewTicketDTO;
use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PriorityTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use App\Models\User;
use App\Notifications\TicketCreated;
use DateTime;
use Illuminate\Support\Facades\Notification;

class AddNewTicket
{
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function execute(NewTicketDTO $dto): int
    {
        // Créer une nouvelle instance de Ticket avec les données du DTO
        $user = new IdentiteUser(
            $dto->userId,
            $dto->userLastName,
            $dto->userFirstName,
            $dto->userEmail,   
            $dto->userType
        );
        
        $ticket = new Ticket(
            0, // ID temporaire, sera défini lors de la persistance
            $dto->title,
            $dto->description,
            StatutTicket::NEW,
            PriorityTicket::fromString($dto->priority),
            $user,
            $dto->categoryId,
            new DateTime(),  // creationDate
            new DateTime()  
        );

        // Only assign a technician if a technician ID is provided
        $technician = null;
        if ($dto->technicianId !== null) {
            $technician = new IdentiteUser(
                $dto->technicianId,
                $dto->technicianLastName ?? '',
                $dto->technicianFirstName ?? '',
                $dto->technicianEmail ?? '',
                'technician'
            );
            $ticket->assignTechnician($technician);
        }

        // Persister le ticket via le repository
        $ticketId = $this->ticketRepository->save($ticket);
        
        // Set the correct ID on the ticket entity
        $ticket = $this->ticketRepository->findById($ticketId);
        
        // Send notification to admin and technicians
        $usersToNotify = User::where('user_type', 'administrator')
            ->orWhere('user_type', 'supervisor')
            ->get();
            
        // Also notify the assigned technician if there is one
        if ($technician) {
            $technicianUser = User::find($technician->getId());
            if ($technicianUser && !$usersToNotify->contains($technicianUser)) {
                $usersToNotify->push($technicianUser);
            }
        }
        
        Notification::send($usersToNotify, new TicketCreated($ticket, $user));
        
        return $ticketId;
    }
}