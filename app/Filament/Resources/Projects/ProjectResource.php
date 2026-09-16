<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Pages\ProjectBoard;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationLabel = 'Proyectos';

    protected static ?string $pluralModelLabel = 'Proyectos';

    protected static ?string $modelLabel = 'Proyecto';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Trabajo';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleTo(auth()->user())
            ->with(['owner', 'leader'])
            ->withCount('tasks');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getRecordUrl($record): ?string
    {
        return ProjectBoard::getUrl(['project' => $record]);
    }
}
