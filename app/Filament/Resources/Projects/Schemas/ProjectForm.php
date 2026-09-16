<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Encabezado del proyecto')
                ->description('Las fechas y el líder pueden quedar pendientes en una copia clonada.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del proyecto')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('start_date')
                        ->label('Fecha de inicio')
                        ->required(fn ($record): bool => ! $record?->isCloneDraft())
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    DatePicker::make('end_date')
                        ->label('Fecha final planeada')
                        ->required(fn ($record): bool => ! $record?->isCloneDraft())
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('start_date'),
                    Select::make('leader_id')
                        ->label('Líder')
                        ->options(fn (): array => User::query()->assignable()->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->default(fn (): ?int => auth()->id())
                        ->required(fn ($record): bool => ! $record?->isCloneDraft()),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),
        ]);
    }
}
