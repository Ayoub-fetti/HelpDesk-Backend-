<?php

namespace App\Domains\Shared\ValueObjects;

class IdentiteUser
{
    private int $id;
    private string $lastName;
    private string $firstName;
    private string $email;
    private string $userType;

    public function __construct(
        int $id,
        string $lastName,
        string $firstName,
        string $email,
        string $userType
    ) {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->userType = $userType;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function isTechnician(): bool
    {
        return $this->userType === 'technician';
    }

    public function isAdministrator(): bool
    {
        return $this->userType === 'administrator';
    }

    public function isSupervisor(): bool
    {
        return $this->userType === 'supervisor';
    }

    public function isFinalUser(): bool
    {
        return $this->userType === 'final_user';
    }
}