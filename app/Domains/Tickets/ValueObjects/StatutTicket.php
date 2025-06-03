<?php

namespace App\Domains\Tickets\ValueObjects;

enum StatutTicket
{
    case NEW;
    case ASSIGNED;
    case IN_PROGRESS;
    case ON_HOLD;
    case RESOLVED;
    case CLOSED;
    case REOPEN;

    public static function fromString(string $statut): self
    {
        return match (strtolower($statut)) {
            'new' => self::NEW,
            'assigned', '' => self::ASSIGNED,
            'in_progress' => self::IN_PROGRESS,
            'on_hold' => self::ON_HOLD,
            'resolved' => self::RESOLVED,
            'closed'=> self::CLOSED,
            'reopen' => self::REOPEN,
            default => throw new \InvalidArgumentException('Invalid ticket status')
        };
    }

    public function toString(): string
    {
        return match($this) {
            self::NEW => 'new',
            self::ASSIGNED => 'assigned',
            self::IN_PROGRESS => 'in_progress',
            self::ON_HOLD => 'on_hold',
            self::RESOLVED => 'resolved',
            self::CLOSED => 'closed',
            self::REOPEN => 'reopen',
        };
    }
}