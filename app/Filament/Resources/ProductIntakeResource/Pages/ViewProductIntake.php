<?php

namespace App\Filament\Resources\ProductIntakeResource\Pages;

use App\Filament\Resources\ProductIntakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductIntake extends ViewRecord
{
    protected static string $resource = ProductIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
    public function getHeading(): string
    {
        return 'Bevételezés Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Bevételezés Megtekintése';
    }
}
