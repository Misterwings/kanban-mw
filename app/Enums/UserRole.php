<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Collaborator = 'collaborator';
    case Observer = 'observer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Collaborator => 'Colaborador',
            self::Observer => 'Observador',
        };
    }
}
