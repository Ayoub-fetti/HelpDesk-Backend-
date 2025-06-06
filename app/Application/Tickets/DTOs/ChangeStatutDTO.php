<?php

namespace App\Application\Tickets\DTOs;

class ChangeStatutDTO
{
    public int $ticketId;
    public string $newStatut;
    public string $comment;
    
    // User information making the change
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userType;
    
    public function __construct(
        int $ticketId,
        string $newStatut,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userType,
        string $userEmail,
        string $comment = ''
    ) {
        $this->ticketId = $ticketId;
        $this->newStatut = $newStatut;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userType = $userType;
        $this->userEmail = $userEmail;
        $this->comment = $comment;
    }
}