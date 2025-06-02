<?php

namespace App\Domains\Tickets\Entities;

use DateTime;

class Commentaire
{
    private int $id;
    private int $ticketId;
    private int $auteurId;
    private string $contenu;
    private bool $estPrive;
    private DateTime $dateCreation;
    private ?DateTime $dateModification = null;

    public function __construct(
        int $id,
        int $ticketId,
        int $auteurId,
        string $contenu,
        bool $estPrive = false,
        ?DateTime $dateCreation = null
    ) {
        $this->id = $id;
        $this->ticketId = $ticketId;
        $this->auteurId = $auteurId;
        $this->contenu = $contenu;
        $this->estPrive = $estPrive;
        $this->dateCreation = $dateCreation ?? new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }

    public function getAuteurId(): int
    {
        return $this->auteurId;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function estPrive(): bool
    {
        return $this->estPrive;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    public function getDateModification(): ?DateTime
    {
        return $this->dateModification;
    }

    public function modifier(string $nouveauContenu): void
    {
        if (trim($nouveauContenu) === '') {
            throw new \InvalidArgumentException('Le contenu du commentaire ne peut pas être vide');
        }

        $this->contenu = $nouveauContenu;
        $this->dateModification = new DateTime();
    }

    public function changerVisibilite(bool $estPrive): void
    {
        $this->estPrive = $estPrive;
    }
    
    
    // Crée une représentation sous forme de tableau de l'entité
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticketId,
            'auteur_id' => $this->auteurId,
            'contenu' => $this->contenu,
            'est_prive' => $this->estPrive,
            'date_creation' => $this->dateCreation->format('Y-m-d H:i:s'),
            'date_modification' => $this->dateModification ? $this->dateModification->format('Y-m-d H:i:s') : null,
        ];
    }
    
    
    //  Crée une entité à partir d'un tableau de données

    public static function fromArray(array $data): self
    {
        $commentaire = new self(
            $data['id'],
            $data['ticket_id'],
            $data['auteur_id'],
            $data['contenu'],
            $data['est_prive'] ?? false,
            isset($data['date_creation']) ? new DateTime($data['date_creation']) : null
        );
        
        if (isset($data['date_modification']) && $data['date_modification']) {
            $commentaire->dateModification = new DateTime($data['date_modification']);
        }
        
        return $commentaire;
    }
}