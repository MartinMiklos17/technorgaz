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
            Actions\EditAction::make(),
        ];
    }
}
