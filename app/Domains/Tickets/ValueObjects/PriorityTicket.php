<?php

namespace App\Domains\Tickets\ValueObjects;

enum PriorityTicket
{
    case LOW;
    case AVERAGE;
    case HIGH;
    case URGENT;

    public static function fromString(string $priority): self
    {
        return match (strtolower($priority)) {
            'low' => self::LOW,
            'average' => self::AVERAGE,
            'high' => self::HIGH,
            'urgent' => self::URGENT,
            default => throw new \InvalidArgumentException('Invalid ticket priority')
        };
    }

    public function toString(): string
    {
        return match($this) {
            self::LOW => 'low',
            self::AVERAGE => 'average',
            self::HIGH => 'high',
            self::URGENT => 'urgent',
        };
    }
}