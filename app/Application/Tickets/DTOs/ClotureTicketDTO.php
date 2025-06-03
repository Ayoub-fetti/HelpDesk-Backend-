<?php

namespace App\Application\Tickets\DTOs;

class ClotureTicketDTO
{
    public int $ticketId;
    public ?string $solution;
    public string $commentaire;
    
    // Informations sur l'utilisateur effectuant la clôture
    public int $utilisateurId;
    public string $utilisateurNom;
    public string $utilisateurPrenom;
    public string $utilisateurEmail;
    public string $utilisateurType;
    
    public function __construct(
        int $ticketId,
        int $utilisateurId,
        string $utilisateurNom,
        string $utilisateurPrenom,
        string $utilisateurType,
        string $utilisateurEmail,
        ?string $solution = null,
        string $commentaire = ''
    ) {
        $this->ticketId = $ticketId;
        $this->utilisateurId = $utilisateurId;
        $this->utilisateurNom = $utilisateurNom;
        $this->utilisateurPrenom = $utilisateurPrenom;
        $this->utilisateurType = $utilisateurType;
        $this->utilisateurEmail = $utilisateurEmail;
        $this->solution = $solution;
        $this->commentaire = $commentaire;
    }
}