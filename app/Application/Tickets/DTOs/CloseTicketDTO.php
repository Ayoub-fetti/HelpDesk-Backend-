<?php

namespace App\Application\Tickets\DTOs;

class CloseTicketDTO
{
    public int $ticketId;
    public ?string $solution;
    public string $comment;
    
    // Informations sur l'utilisateur effectuant la clôture
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userType;
    
    public function __construct(
        int $ticketId,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userType,
        string $userEmail,
        ?string $solution = null,
        string $comment = ''
    ) {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userType = $userType;
        $this->userEmail = $userEmail;
        $this->solution = $solution;
        $this->comment = $comment;
    }
}