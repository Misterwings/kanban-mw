<?php

namespace App\Enums;

enum ProjectEntryType: string
{
    case Note = 'note';
    case Observation = 'observation';

    public function label(): string
    {
        return match ($this) {
            self::Note => 'Nota de seguimiento',
            self::Observation => 'Observación',
        };
    }
}
