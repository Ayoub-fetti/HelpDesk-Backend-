<?php

namespace App\Domains\Tickets\Entities;

use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PriorityTicket;
use App\Domains\Shared\ValueObjects\IdentiteUser;
use DateTime;

class Ticket 
{
    private int $id;
    private string $title;
    private string $description;
    private StatutTicket $statut;
    private PriorityTicket $priority;
    private IdentiteUser $user;
    private ?IdentiteUser $technician = null;
    private int $categoryId;
    private DateTime $creationDate;
    private ?DateTime $resolutionDate = null;
    private ?string $solution = null;
    private float $timePass = 0;
    
    private array $comments = [];
    private array $attachments = [];
    private ?float $assessment = null; 

    public function __construct(
        int $id,
        string $title,
        string $description,
        StatutTicket $statut,
        PriorityTicket $priority,
        IdentiteUser $user,
        int $categoryId,
        DateTime $creationDate
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->statut = $statut;
        $this->priority = $priority;
        $this->user = $user;
        $this->categoryId = $categoryId;
        $this->creationDate = $creationDate;
    }

    // Getters et setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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

    public function getPriority(): PriorityTicket
    {
        return $this->priority;
    }
    public function setPriority($priority) 
    {
        $this->priority = $priority;
    }

    // Méthodes spécifiques pour les priorités, similaires à celles pour les statuts
    public function markPriorityLow(): void
    {
        $this->priority = PriorityTicket::LOW;
    }

    public function markPriorityAverage(): void
    {
        $this->priority = PriorityTicket::AVERAGE;
    }

    public function markPriorityHigh(): void
    {
        $this->priority = PriorityTicket::HIGH;
    }

    public function markPriorityUrgent(): void
    {
        $this->priority = PriorityTicket::URGENT;
    }

    public function getUser(): IdentiteUser
    {
        return $this->user;
    }

    public function getTechnician(): ?IdentiteUser
    {
        return $this->technician;
    }

    public function assignTechnician(IdentiteUser $technician): void
    {
        $this->technician = $technician;
        // $this->statut = StatutTicket::ASSIGNED;
    }

    public function withdrawTechnician(): void
    {
        $this->technician = null;
        $this->statut = StatutTicket::NEW;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getResolutionDate(): ?DateTime
    {
        return $this->resolutionDate;
    }

    public function setResolutionDate($resolutionDate)
    {
        $this->resolutionDate = $resolutionDate;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution($solution)
    {
        $this->solution = $solution;
    }
    

    public function solve(string $solution): void
    {
        $this->solution = $solution;
        $this->resolutionDate = new DateTime();
        $this->statut = StatutTicket::RESOLVED;
    }

    public function close(): void
    {
        $this->statut = StatutTicket::CLOSED;
    }

    public function reopen(): void
    {
        $this->statut = StatutTicket::REOPEN;
        $this->resolutionDate = null;
        $this->solution = null;
    }

    public function markInProgress(): void
    {
        $this->statut = StatutTicket::IN_PROGRESS;
    }

    public function markOnHold(): void
    {
        $this->statut = StatutTicket::ON_HOLD;
    }

    public function getTimePass(): float
    {
        return $this->timePass;
    }

    public function setTimePass($timePass)
    {
        $this->timePass = $timePass;
    }

    public function addTime(float $time): void
    {
        $this->timePass += $time;
    }

    // Gestion des comments
    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(Comment $comments): void
    {
        $this->comments[] = $comments;
    }

    // Gestion des pièces jointes
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachments): void
    {
        $this->attachments[] = $attachments;
    }

    // Gestion de l'évaluation
    public function getAssessment(): ?float
    {
        return $this->assessment;
    }

    public function assess(float $assessment): void
    {
        if ($assessment < 0 || $assessment > 5) {
            throw new \InvalidArgumentException('The assessment should be between 0 and 5.');
        }
        $this->assessment = $assessment;
    }
}