<?php

namespace App\Filament\Resources\PartnerDetailsResource\Pages;

use App\Filament\Resources\PartnerDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerDetails extends ViewRecord
{
    protected static string $resource = PartnerDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Szerkesztés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Partner Adatok Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Partner Adatok Megtekintése';
    }
}
