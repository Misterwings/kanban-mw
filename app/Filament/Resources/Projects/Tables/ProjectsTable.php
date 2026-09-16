<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectStatus;
use App\Filament\Pages\ProjectBoard;
use App\Models\Project;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (ProjectStatus $state): string => $state->label())
                    ->color(fn (ProjectStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('leader.name')
                    ->label('Líder')
                    ->placeholder('Pendiente'),
                TextColumn::make('owner.name')
                    ->label('Propietario')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tasks_count')
                    ->label('Tareas')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin planeado')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Finalizado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendiente')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([])
            ->recordUrl(fn (Project $record): string => ProjectBoard::getUrl(['project' => $record]));
    }
}
