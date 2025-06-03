<?php

namespace App\Application\Tickets\DTOs;

class ReassignationTicketDTO
{
    public int $ticketId;
    public string $commentaire;
    
    // Informations sur l'utilisateur effectuant la réassignation
    public int $utilisateurId;
    public string $utilisateurNom;
    public string $utilisateurPrenom;
    public string $utilisateurEmail;
    public string $utilisateurType;
    
    // Informations sur le technicien à assigner
    public int $technicienId;
    public string $technicienNom;
    public string $technicienPrenom;
    public string $technicienEmail;
    public string $technicienTelephone;
    
    public function __construct(
        int $ticketId,
        int $technicienId,
        string $technicienNom,
        string $technicienPrenom,
        string $technicienEmail,
        int $utilisateurId,
        string $utilisateurNom,
        string $utilisateurPrenom,
        string $utilisateurType,
        string $utilisateurEmail,
        string $technicienTelephone = '',
        string $commentaire = ''
    ) {
        $this->ticketId = $ticketId;
        $this->technicienId = $technicienId;
        $this->technicienNom = $technicienNom;
        $this->technicienPrenom = $technicienPrenom;
        $this->technicienEmail = $technicienEmail;
        $this->technicienTelephone = $technicienTelephone;
        $this->utilisateurId = $utilisateurId;
        $this->utilisateurNom = $utilisateurNom;
        $this->utilisateurPrenom = $utilisateurPrenom;
        $this->utilisateurType = $utilisateurType;
        $this->utilisateurEmail = $utilisateurEmail;
        $this->commentaire = $commentaire;
    }
}