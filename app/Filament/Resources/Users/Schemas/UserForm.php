<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de acceso')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('role')
                        ->label('Perfil')
                        ->options(collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])->all())
                        ->required()
                        ->native(false),
                    Toggle::make('is_active')
                        ->label('Usuario activo')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),
            Section::make('Contraseña')
                ->description('En edición, déjalo vacío para conservar la contraseña actual.')
                ->schema([
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->minLength(8)
                        ->confirmed()
                        ->autocomplete('new-password'),
                    TextInput::make('password_confirmation')
                        ->label('Confirmar contraseña')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(false)
                        ->autocomplete('new-password'),
                ])
                ->columns(2),
        ]);
    }
}
