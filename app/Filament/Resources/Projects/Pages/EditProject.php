<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Pages\ProjectBoard;
use App\Filament\Resources\Projects\ProjectResource;
use App\Services\ProjectService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProjectService::class)->updateProject($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return ProjectBoard::getUrl(['project' => $this->record]);
    }
}
