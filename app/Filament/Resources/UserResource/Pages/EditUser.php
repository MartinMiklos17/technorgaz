<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
            Actions\DeleteAction::make()->label('Törlés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Felhasználó szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Felhasználó szerkesztése';
    }
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Mentés')
                ->action('save')
                ->color('primary'),

            Actions\Action::make('cancel')
                ->label('Mégse')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
