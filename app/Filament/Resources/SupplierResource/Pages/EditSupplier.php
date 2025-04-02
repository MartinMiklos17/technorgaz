<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
            Actions\DeleteAction::make()->label('Törlés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Beszállító szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Beszállító szerkesztése';
    }
}
