<?php

namespace App\Application\Tickets\DTOs;

class ReassignationTicketDTO
{
    public int $ticketId;
    public string $comment;
    
    // User information performing the reassignment    
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userType;
    
    // Information on the technician to assign    
    public int $technicianId;
    public ?string $technicianLastName;
    public ?string $technicianFirstName;
    public ?string $technicianEmail;
    public ?string $technicianPhone;
    
    public function __construct(
        int $ticketId,
        int $technicianId,
        ?string $technicianLastName = null,
        ?string $technicianFirstName = null,
        ?string $technicianEmail = null,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userType,
        string $userEmail,
        string $technicianPhone = '',
        string $comment = ''
    ) {
        $this->ticketId = $ticketId;
        $this->technicianId = $technicianId;
        $this->technicianLastName = $technicianLastName ?? '';
        $this->technicianFirstName = $technicianFirstName ?? '';
        $this->technicianEmail = $technicianEmail ?? '';
        $this->technicianPhone = $technicianPhone;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userType = $userType;
        $this->userEmail = $userEmail;
        $this->comment = $comment;
    }
}