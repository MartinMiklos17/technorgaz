<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Filament\Resources\ProductOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductOrder extends ViewRecord
{
    protected static string $resource = ProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
        public function getHeading(): string
    {
        return 'Termék Rendelés Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék Rendelés Megtekintése';
    }
}
