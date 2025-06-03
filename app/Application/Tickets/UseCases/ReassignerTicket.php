<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\ReassignationTicketDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;

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
        $utilisateurEffectuantAction = new IdentiteUtilisateur(
            $dto->utilisateurId,
            $dto->utilisateurNom,
            $dto->utilisateurPrenom,
            $dto->utilisateurEmail,  
            $dto->utilisateurType
        );

        // Créer l'identité du technicien à assigner
        $technicien = new IdentiteUtilisateur(
            $dto->technicienId,
            $dto->technicienNom,
            'technicien',
            $dto->technicienEmail,
            $dto->technicienTelephone
        );

        // Appeler le service de domaine pour assigner le technicien
        $this->ticketService->assignerTechnicien($ticket, $technicien, $utilisateurEffectuantAction);
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
    }
}