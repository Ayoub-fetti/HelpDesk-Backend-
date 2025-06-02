<?php

namespace App\Domains\Tickets\ValueObjects;

enum StatutTicket
{
    case NOUVEAU;
    case ASSIGNE;
    case EN_COURS;
    case EN_ATTENTE;
    case RESOLU;
    case FERME;
    case ROUVERT;

    public static function fromString(string $statut): self
    {
        return match (strtolower($statut)) {
            'nouveau' => self::NOUVEAU,
            'assigné', 'assigne' => self::ASSIGNE,
            'en_cours' => self::EN_COURS,
            'en_attente' => self::EN_ATTENTE,
            'résolu', 'resolu' => self::RESOLU,
            'fermé', 'ferme' => self::FERME,
            'rouvert' => self::ROUVERT,
            default => throw new \InvalidArgumentException('Statut de ticket invalide')
        };
    }

    public function toString(): string
    {
        return match($this) {
            self::NOUVEAU => 'nouveau',
            self::ASSIGNE => 'assigné',
            self::EN_COURS => 'en_cours',
            self::EN_ATTENTE => 'en_attente',
            self::RESOLU => 'résolu',
            self::FERME => 'fermé',
            self::ROUVERT => 'rouvert',
        };
    }
}