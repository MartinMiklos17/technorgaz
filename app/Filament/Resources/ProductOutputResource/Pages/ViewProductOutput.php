<?php

namespace App\Filament\Resources\ProductOutputResource\Pages;

use App\Filament\Resources\ProductOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductOutput extends ViewRecord
{
    protected static string $resource = ProductOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
    public function getHeading(): string
    {
        return 'Kiadás Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Kiadás Megtekintése';
    }
}
