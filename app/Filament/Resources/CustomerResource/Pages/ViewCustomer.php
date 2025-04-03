<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
    public function getHeading(): string
    {
        return 'Vevő Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Vevő Megtekintése';
    }
}
