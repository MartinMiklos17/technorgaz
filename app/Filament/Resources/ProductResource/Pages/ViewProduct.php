<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
    public function getHeading(): string
    {
        return 'Termék Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék Megtekintése';
    }
}
