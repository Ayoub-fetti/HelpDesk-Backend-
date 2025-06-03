<?php

namespace App\Application\Tickets\DTOs;

class NewCommentDTO
{
    public int $ticketId;
    public string $content;
    public bool $isPrivate;
    
    // Informations sur l'utilisateur ajoutant le commentaire
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userType;
    
    public function __construct(
        int $ticketId,
        string $content,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userType,
        string $userEmail,
        bool $isPrivate = false
    ) {
        $this->ticketId = $ticketId;
        $this->content = $content;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userType = $userType;
        $this->userEmail = $userEmail;
        $this->isPrivate = $isPrivate;
    }
}