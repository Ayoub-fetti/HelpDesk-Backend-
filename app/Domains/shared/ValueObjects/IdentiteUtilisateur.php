<?php

namespace App\Domains\Shared\ValueObjects;

class IdentiteUtilisateur
{
    private int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $typeUtilisateur;

    public function __construct(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        string $typeUtilisateur
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->typeUtilisateur = $typeUtilisateur;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTypeUtilisateur(): string
    {
        return $this->typeUtilisateur;
    }

    public function estTechnicien(): bool
    {
        return $this->typeUtilisateur === 'technicien';
    }

    public function estAdministrateur(): bool
    {
        return $this->typeUtilisateur === 'administrateur';
    }

    public function estSuperviseur(): bool
    {
        return $this->typeUtilisateur === 'superviseur';
    }

    public function estUtilisateurFinal(): bool
    {
        return $this->typeUtilisateur === 'utilisateur_final';
    }
}