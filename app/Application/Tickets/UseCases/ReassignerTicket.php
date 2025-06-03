<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\ReassignationTicketDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUser;

class ReassignerTicket
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

    public function execute(ReassignationTicketDTO $dto): void
    {
        // Récupérer le ticket depuis le repository
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        
        if (!$ticket) {
            throw new \InvalidArgumentException("Le ticket #{$dto->ticketId} n'existe pas");
        }

        // Créer l'identité de l'utilisateur effectuant l'action
        $userEffectuantAction = new IdentiteUser(
            $dto->userId,
            $dto->userLastName,
            $dto->userFirstName,
            $dto->userEmail,  
            $dto->userType
        );

        // Créer l'identité du technicien à assigner
        $technician = new IdentiteUser(
            $dto->technicianId,
            $dto->technicianLastName,
            $dto->technicianFirstName,
            'technicien',
            $dto->technicianEmail,
            $dto->technicianPhone
        );

        // Appeler le service de domaine pour assigner le technicien
        $this->ticketService->assignTechnician($ticket, $technician, $userEffectuantAction);
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
    }
}