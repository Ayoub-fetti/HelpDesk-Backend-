<?php

namespace App\Application\Tickets\DTOs;

class ResolveTicketDTO
{
    public int $ticketId;
    public string $solution;
    public string $comment;
    
    // Informations sur l'utilisateur effectuant la résolution
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userType;
    
    public function __construct(
        int $ticketId,
        string $solution,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userType,
        string $userEmail,
        string $comment = ''
    ) {
        $this->ticketId = $ticketId;
        $this->solution = $solution;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userType = $userType;
        $this->userEmail = $userEmail;
        $this->comment = $comment;
    }
}