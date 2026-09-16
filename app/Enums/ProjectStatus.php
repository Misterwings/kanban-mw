<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Created = 'created';
    case InProgress = 'in_progress';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creado',
            self::InProgress => 'En proceso',
            self::Finished => 'Finalizado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Created => 'gray',
            self::InProgress => 'warning',
            self::Finished => 'success',
        };
    }
}
