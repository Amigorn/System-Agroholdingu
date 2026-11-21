<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Zadanie utworzone — dodaj zestaw roboczy')
            ->body('Przypisz co najmniej jeden zestaw roboczy, aby zadanie było kompletne i gotowe do rozpoczęcia.')
            ->warning();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', [
            'record' => $this->record,
        ]);
    }
}