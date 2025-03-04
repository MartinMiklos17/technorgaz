<?php

namespace App\Filament\Resources\PartnerDetailsResource\Pages;

use App\Filament\Resources\PartnerDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartnerDetails extends EditRecord
{
    protected static string $resource = PartnerDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
