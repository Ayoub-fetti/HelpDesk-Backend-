<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\ChangementStatutDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;

class ChangerStatutTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(TicketRepositoryInterface $ticketRepository,TicketService $ticketService) {
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    public function execute(ChangementStatutDTO $dto): void
    {
        // Récupérer le ticket depuis le repository
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        
        if (!$ticket) {
            throw new \InvalidArgumentException("Le ticket #{$dto->ticketId} n'existe pas");
        }

        // Créer l'identité de l'utilisateur effectuant l'action
        $utilisateur = new IdentiteUtilisateur(
            $dto->utilisateurId,
            $dto->utilisateurNom,
            $dto->utilisateurPrenom,
            $dto->utilisateurEmail,  
            $dto->utilisateurType
        );

        // Convertir la chaîne de statut en objet StatutTicket
        $nouveauStatut = StatutTicket::fromString($dto->nouveauStatut);
        
        // Appeler le service de domaine pour changer le statut
        $this->ticketService->changerStatut($ticket, $nouveauStatut, $utilisateur);
        
        // Sauvegarder les modifications
        $this->ticketRepository->update($ticket);
    }
}