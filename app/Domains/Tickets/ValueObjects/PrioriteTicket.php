<?php

namespace App\Domains\Tickets\ValueObjects;

enum PrioriteTicket
{
    case BASSE;
    case MOYENNE;
    case HAUTE;
    case URGENTE;

    public static function fromString(string $priorite): self
    {
        return match (strtolower($priorite)) {
            'basse' => self::BASSE,
            'moyenne' => self::MOYENNE,
            'haute' => self::HAUTE,
            'urgente' => self::URGENTE,
            default => throw new \InvalidArgumentException('Priorité de ticket invalide')
        };
    }

    public function toString(): string
    {
        return match($this) {
            self::BASSE => 'basse',
            self::MOYENNE => 'moyenne',
            self::HAUTE => 'haute',
            self::URGENTE => 'urgente',
        };
    }
}