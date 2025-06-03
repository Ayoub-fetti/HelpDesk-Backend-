<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NouveauCommentaireDTO;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\Services\TicketService;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;

class AjouterCommentaireTicket
{
    private $ticketRepository;
    private $ticketService;

    public function __construct(TicketRepositoryInterface $ticketRepository,TicketService $ticketService) {
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    public function execute(NouveauCommentaireDTO $dto): int
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

        // Appeler le service de domaine pour ajouter le commentaire
        $this->ticketService->ajouterCommentaire(
            $ticket,
            $dto->contenu,
            $utilisateur,
            $dto->estPrive ?? false
        );
        
        // Sauvegarder les modifications et récupérer l'ID du commentaire
        $commentaireId = $this->ticketRepository->update($ticket);
        
        return $commentaireId;
    }
}