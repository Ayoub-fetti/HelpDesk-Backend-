<?php

namespace App\Domains\Tickets\Exceptions;

use App\Domains\Tickets\ValueObjects\StatutTicket;
use Exception;

class StatutInvalideException extends Exception
{
    private ?StatutTicket $statutActuel;
    private ?StatutTicket $statutDemande;

    public function __construct(string $message = "", ?StatutTicket $statutActuel = null, ?StatutTicket $statutDemande = null, int $code = 0, ?Exception $previous = null)
    {
        $this->statutActuel = $statutActuel;
        $this->statutDemande = $statutDemande;
        
        // Si les statuts sont fournis mais pas le message, créer un message par défaut
        if (empty($message) && $statutActuel && $statutDemande) {
            $message = "The transition of status from '{$statutActuel->toString()}' to '{$statutDemande->toString()}' is not allowed";
        }
        
        parent::__construct($message, $code, $previous);
    }

    public function getStatutActuel(): ?StatutTicket
    {
        return $this->statutActuel;
    }

    public function getStatutDemande(): ?StatutTicket
    {
        return $this->statutDemande;
    }
}