<?php

namespace App\Domains\Tickets\Exceptions;

use Exception;

class RegleMetierException extends Exception
{
    private string $regle;
    private array $contexte;

    public function __construct(string $message = "", string $regle = "", array $contexte = [], int $code = 0, ?Exception $previous = null)
    {
        $this->regle = $regle;
        $this->contexte = $contexte;
        
        parent::__construct($message, $code, $previous);
    }

    public function getRegle(): string
    {
        return $this->regle;
    }

    public function getContexte(): array
    {
        return $this->contexte;
    }
    
    /**
     * Crée une exception pour une résolution de ticket sans solution
     */
    public static function resolutionSansSolution(): self
    {
        return new self(
            'La solution ne peut pas être vide pour résoudre un ticket',
            'resolution_requiert_solution',
            []
        );
    }
    
    /**
     * Crée une exception pour un commentaire vide
     */
    public static function commentaireVide(): self
    {
        return new self(
            'Le contenu du commentaire ne peut pas être vide',
            'commentaire_non_vide',
            []
        );
    }
    
    /**
     * Crée une exception pour un problème d'autorisation
     */
    public static function autorisationInsuffisante(string $action, string $role): self
    {
        return new self(
            "Autorisation insuffisante pour {$action}",
            'autorisation_requise',
            ['action' => $action, 'role_requis' => $role]
        );
    }
}