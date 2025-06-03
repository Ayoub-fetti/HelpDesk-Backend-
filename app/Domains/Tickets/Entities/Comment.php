<?php

namespace App\Domains\Tickets\Entities;

use DateTime;

class Comment
{
    private int $id;
    private int $ticketId;
    private int $authorId;
    private string $content;
    private bool $isPrivate;
    private DateTime $creationDate;
    private ?DateTime $modificationDate = null;

    public function __construct(
        int $id,
        int $ticketId,
        int $authorId,
        string $content,
        bool $isPrivate = false,
        ?DateTime $creationDate = null
    ) {
        $this->id = $id;
        $this->ticketId = $ticketId;
        $this->authorId = $authorId;
        $this->content = $content;
        $this->isPrivate = $isPrivate;
        $this->creationDate = $creationDate ?? new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function getcreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getmodificationDate(): ?DateTime
    {
        return $this->modificationDate;
    }
    public function setmodificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function edit(string $newContent): void
    {
        if (trim($newContent) === '') {
            throw new \InvalidArgumentException('Comment content cannot be empty');
        }

        $this->content = $newContent;
        $this->modificationDate = new DateTime();
    }

    public function changevisibility(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }
    
    
    // Crée une représentation sous forme de tableau de l'entité
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticketId,
            'author_id' => $this->authorId,
            'content' => $this->content,
            'is_private' => $this->isPrivate,
            'creation_date' => $this->creationDate->format('Y-m-d H:i:s'),
            'modification_date' => $this->modificationDate ? $this->modificationDate->format('Y-m-d H:i:s') : null,
        ];
    }
    
    
    //  Crée une entité à partir d'un tableau de données

    public static function fromArray(array $data): self
    {
        $comment = new self(
            $data['id'],
            $data['ticket_id'],
            $data['author_id'],
            $data['content'],
            $data['is_private'] ?? false,
            isset($data['creation_date']) ? new DateTime($data['creation_date']) : null
        );
        
        if (isset($data['modification_date']) && $data['modification_date']) {
            $comment->modificationDate = new DateTime($data['date_modification']);
        }
        
        return $comment;
    }
}