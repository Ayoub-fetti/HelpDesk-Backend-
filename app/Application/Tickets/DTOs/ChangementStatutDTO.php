<?php

namespace App\Application\Tickets\DTOs;

class ChangementStatutDTO
{
    public int $ticketId;
    public string $nouveauStatut;
    public string $commentaire;
    
    // Informations sur l'utilisateur effectuant le changement
    public int $utilisateurId;
    public string $utilisateurNom;
    public string $utilisateurPrenom;
    public string $utilisateurEmail;
    public string $utilisateurType;
    
    public function __construct(
        int $ticketId,
        string $nouveauStatut,
        int $utilisateurId,
        string $utilisateurNom,
        string $utilisateurPrenom,
        string $utilisateurType,
        string $utilisateurEmail,
        string $commentaire = ''
    ) {
        $this->ticketId = $ticketId;
        $this->nouveauStatut = $nouveauStatut;
        $this->utilisateurId = $utilisateurId;
        $this->utilisateurNom = $utilisateurNom;
        $this->utilisateurPrenom = $utilisateurPrenom;
        $this->utilisateurType = $utilisateurType;
        $this->utilisateurEmail = $utilisateurEmail;
        $this->commentaire = $commentaire;
    }
}