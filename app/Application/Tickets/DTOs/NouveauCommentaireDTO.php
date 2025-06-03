<?php

namespace App\Application\Tickets\DTOs;

class NouveauCommentaireDTO
{
    public int $ticketId;
    public string $contenu;
    public bool $estPrive;
    
    // Informations sur l'utilisateur ajoutant le commentaire
    public int $utilisateurId;
    public string $utilisateurNom;
    public string $utilisateurPrenom;
    public string $utilisateurEmail;
    public string $utilisateurType;
    
    public function __construct(
        int $ticketId,
        string $contenu,
        int $utilisateurId,
        string $utilisateurNom,
        string $utilisateurPrenom,
        string $utilisateurType,
        string $utilisateurEmail,
        bool $estPrive = false
    ) {
        $this->ticketId = $ticketId;
        $this->contenu = $contenu;
        $this->utilisateurId = $utilisateurId;
        $this->utilisateurNom = $utilisateurNom;
        $this->utilisateurPrenom = $utilisateurPrenom;
        $this->utilisateurType = $utilisateurType;
        $this->utilisateurEmail = $utilisateurEmail;
        $this->estPrive = $estPrive;
    }
}