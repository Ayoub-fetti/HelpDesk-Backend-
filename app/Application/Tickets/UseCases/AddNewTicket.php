<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NewTicketDTO;
use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PriorityTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use DateTime;

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
        $ticket = new Ticket(
            0, // ID temporaire, sera défini lors de la persistance
            $dto->title,
            $dto->description,
            StatutTicket::NEW,
            PriorityTicket::fromString($dto->priority),
            new IdentiteUser(
            $dto->userId,
            $dto->userLastName,
            $dto->userFirstName,
            $dto->userEmail,  
            $dto->userPhone,  
            $dto->userType
            ),
            $dto->categoryId,
            new DateTime()
        );

        $ticket->assignTechnician(new IdentiteUser(
            $dto->technicianId,
            $dto->technicianLastName ?? '',
            $dto->technicianFirstName ?? '',
            'technician',
            $dto->technicianEmail ?? '',
            $dto->technicianPhone ?? ''
        ));

        // Persister le ticket via le repository
        $ticketId = $this->ticketRepository->save($ticket);
        
        return $ticketId;
    }
}