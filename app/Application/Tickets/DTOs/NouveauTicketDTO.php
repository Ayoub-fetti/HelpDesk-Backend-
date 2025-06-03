<?php

namespace App\Application\Tickets\DTOs;

class NouveauTicketDTO
{
    public string $titre;
    public string $description;
    public string $priorite;
    public int $categorieId;
    
    // Informations sur l'utilisateur qui crée le ticket
    public int $utilisateurId;
    public string $utilisateurNom;
    public string $utilisateurPrenom;
    public string $utilisateurEmail;
    public string $utilisateurTelephone;
    public string $utilisateurType;
    
    // Informations sur le technicien assigné (optionnel)
    public ?int $technicienId = null;
    public ?string $technicienNom = null;
    public ?string $technicienPrenom = null;
    public ?string $technicienEmail = null;
    public ?string $technicienTelephone = null;
    
    public function __construct(
        string $titre,
        string $description,
        string $priorite,
        int $categorieId,
        int $utilisateurId,
        string $utilisateurNom,
        string $utilisateurPrenom,
        string $utilisateurEmail,
        string $utilisateurType,
        string $utilisateurTelephone = '',
        ?int $technicienId = null,
        ?string $technicienNom = null,
        ?string $technicienPrenom = null,
        ?string $technicienEmail = null,
        ?string $technicienTelephone = null
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->priorite = $priorite;
        $this->categorieId = $categorieId;
        $this->utilisateurId = $utilisateurId;
        $this->utilisateurNom = $utilisateurNom;
        $this->utilisateurPrenom = $utilisateurPrenom;
        $this->utilisateurEmail = $utilisateurEmail;
        $this->utilisateurType = $utilisateurType;
        $this->utilisateurTelephone = $utilisateurTelephone;
        $this->technicienId = $technicienId;
        $this->technicienNom = $technicienNom;
        $this->technicienPrenom = $technicienPrenom;
        $this->technicienEmail = $technicienEmail;
        $this->technicienTelephone = $technicienTelephone;
    }
}