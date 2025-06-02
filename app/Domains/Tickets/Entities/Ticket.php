<?php

namespace App\Domains\Tickets\Entities;

use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PrioriteTicket;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;
use DateTime;

class Ticket 
{
    private int $id;
    private string $titre;
    private string $description;
    private StatutTicket $statut;
    private PrioriteTicket $priorite;
    private IdentiteUtilisateur $utilisateur;
    private ?IdentiteUtilisateur $technicien = null;
    private int $categorieId;
    private DateTime $dateCreation;
    private ?DateTime $dateResolution = null;
    private ?string $solution = null;
    private float $tempsPasse = 0;
    
    private array $commentaires = [];
    private array $piecesJointes = [];
    private ?float $evaluation = null; 

    public function __construct(
        int $id,
        string $titre,
        string $description,
        StatutTicket $statut,
        PrioriteTicket $priorite,
        IdentiteUtilisateur $utilisateur,
        int $categorieId,
        DateTime $dateCreation
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->statut = $statut;
        $this->priorite = $priorite;
        $this->utilisateur = $utilisateur;
        $this->categorieId = $categorieId;
        $this->dateCreation = $dateCreation;
    }

    // Getters et setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatut(): StatutTicket
    {
        return $this->statut;
    }

    public function setStatut(StatutTicket $statut): void
    {
        $this->statut = $statut;
    }

    public function getPriorite(): PrioriteTicket
    {
        return $this->priorite;
    }

    // Méthodes spécifiques pour les priorités, similaires à celles pour les statuts
    public function marquerPrioriteBasse(): void
    {
        $this->priorite = PrioriteTicket::BASSE();
    }

    public function marquerPrioriteMoyenne(): void
    {
        $this->priorite = PrioriteTicket::MOYENNE();
    }

    public function marquerPrioriteHaute(): void
    {
        $this->priorite = PrioriteTicket::HAUTE();
    }

    public function marquerPrioriteUrgente(): void
    {
        $this->priorite = PrioriteTicket::URGENTE();
    }

    public function getUtilisateur(): IdentiteUtilisateur
    {
        return $this->utilisateur;
    }

    public function getTechnicien(): ?IdentiteUtilisateur
    {
        return $this->technicien;
    }

    public function assignerTechnicien(IdentiteUtilisateur $technicien): void
    {
        $this->technicien = $technicien;
        $this->statut = StatutTicket::ASSIGNE();
    }

    public function retirerTechnicien(): void
    {
        $this->technicien = null;
        $this->statut = StatutTicket::NOUVEAU();
    }

    public function getCategorieId(): int
    {
        return $this->categorieId;
    }

    public function setCategorieId(int $categorieId): void
    {
        $this->categorieId = $categorieId;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    public function getDateResolution(): ?DateTime
    {
        return $this->dateResolution;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function resoudre(string $solution): void
    {
        $this->solution = $solution;
        $this->dateResolution = new DateTime();
        $this->statut = StatutTicket::RESOLU();
    }

    public function fermer(): void
    {
        $this->statut = StatutTicket::FERME();
    }

    public function rouvrir(): void
    {
        $this->statut = StatutTicket::ROUVERT();
        $this->dateResolution = null;
        $this->solution = null;
    }

    public function marquerEnCours(): void
    {
        $this->statut = StatutTicket::EN_COURS();
    }

    public function marquerEnAttente(): void
    {
        $this->statut = StatutTicket::EN_ATTENTE();
    }

    public function getTempsPasse(): float
    {
        return $this->tempsPasse;
    }

    public function ajouterTemps(float $temps): void
    {
        $this->tempsPasse += $temps;
    }

    // Gestion des commentaires
    public function getCommentaires(): array
    {
        return $this->commentaires;
    }

    public function ajouterCommentaire(Commentaire $commentaire): void
    {
        $this->commentaires[] = $commentaire;
    }

    // Gestion des pièces jointes
    public function getPiecesJointes(): array
    {
        return $this->piecesJointes;
    }

    public function ajouterPieceJointe(PieceJointe $pieceJointe): void
    {
        $this->piecesJointes[] = $pieceJointe;
    }

    // Gestion de l'évaluation
    public function getEvaluation(): ?float
    {
        return $this->evaluation;
    }

    public function evaluer(float $evaluation): void
    {
        if ($evaluation < 0 || $evaluation > 5) {
            throw new \InvalidArgumentException('L\'évaluation doit être comprise entre 0 et 5.');
        }
        $this->evaluation = $evaluation;
    }
}