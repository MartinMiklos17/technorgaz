<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommissioningLog extends EditRecord
{
    protected static string $resource = CommissioningLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    public function getHeading(): string
    {
        return 'Beüzemelési Napló szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Beüzemelési Napló  szerkesztése';
    }
}
