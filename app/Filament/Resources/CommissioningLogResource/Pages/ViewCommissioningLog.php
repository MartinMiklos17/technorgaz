<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommissioningLog extends ViewRecord
{
    protected static string $resource = CommissioningLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
