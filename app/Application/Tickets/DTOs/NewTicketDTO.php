<?php

namespace App\Application\Tickets\DTOs;

class NewTicketDTO
{
    public string $title;
    public string $description;
    public string $priority;
    public int $categoryId;
    
    // Informations sur l'utilisateur qui crée le ticket
    public int $userId;
    public string $userLastName;
    public string $userFirstName;
    public string $userEmail;
    public string $userPhone;
    public string $userType;
    
    // Informations sur le technicien assigné (optionnel)
    public ?int $technicianId = null;
    public ?string $technicianLastName = null;
    public ?string $technicianFirstName = null;
    public ?string $technicianEmail = null;
    public ?string $technicianPhone = null;
    
    public function __construct(
        string $title,
        string $description,
        string $priority,
        int $categoryId,
        int $userId,
        string $userLastName,
        string $userFirstName,
        string $userEmail,
        string $userType,
        string $userPhone = '',
        ?int $technicianId = null,
        ?string $technicianLastName = null,
        ?string $technicianFirstName = null,
        ?string $technicianEmail = null,
        ?string $technicianPhone = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->categoryId = $categoryId;
        $this->userId = $userId;
        $this->userLastName = $userLastName;
        $this->userFirstName = $userFirstName;
        $this->userEmail = $userEmail;
        $this->userType = $userType;
        $this->userPhone = $userPhone;
        $this->technicianId = $technicianId;
        $this->technicianLastName = $technicianLastName;
        $this->technicianFirstName = $technicianFirstName;
        $this->technicianEmail = $technicianEmail;
        $this->technicianPhone = $technicianPhone;
    }
}