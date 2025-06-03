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
            'Solution cannot be empty to resolve a ticket',
            'resolution_requires_solution',
            []
        );
    }
    
    /**
     * Crée une exception pour un commentaire vide
     */
    public static function commentaireVide(): self
    {
        return new self(
            'Comment content cannot be empty',
            'comment_not_empty',
            []
        );
    }
    
    /**
     * Crée une exception pour un problème d'autorisation
     */
    public static function autorisationInsuffisante(string $action, string $role): self
    {
        return new self(
            "Insufficient authorization for {$action}",
            'authorization_required',
            ['action' => $action, 'role_requis' => $role]
        );
    }
}