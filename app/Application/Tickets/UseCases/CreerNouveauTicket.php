<?php

namespace App\Application\Tickets\UseCases;

use App\Application\Tickets\DTOs\NouveauTicketDTO;
use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PrioriteTicket;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;
use DateTime;

class CreerNouveauTicket
{
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function execute(NouveauTicketDTO $dto): int
    {
        // Créer une nouvelle instance de Ticket avec les données du DTO
        $ticket = new Ticket(
            0, // ID temporaire, sera défini lors de la persistance
            $dto->titre,
            $dto->description,
            StatutTicket::NOUVEAU,
            PrioriteTicket::fromString($dto->priorite),
            new IdentiteUtilisateur(
                $dto->utilisateurId,
                $dto->utilisateurNom,
                $dto->utilisateurType,
                $dto->utilisateurEmail ?? '',
                $dto->utilisateurTelephone ?? ''
            ),
            $dto->categorieId,
            new DateTime()
        );

        $ticket->assignerTechnicien(new IdentiteUtilisateur(
            $dto->technicienId,
            $dto->technicienNom ?? '',
            'technicien',
            $dto->technicienEmail ?? '',
            $dto->technicienTelephone ?? ''
        ));

        // Persister le ticket via le repository
        $ticketId = $this->ticketRepository->save($ticket);
        
        return $ticketId;
    }
}