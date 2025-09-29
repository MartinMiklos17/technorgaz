<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommissioningLogs extends ListRecords
{
    protected static string $resource = CommissioningLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label("Új Beüzemelési Napló"),
        ];
    }
    public function getHeading(): string
    {
        return 'Beüzemelési Naplók';
    }
    public function getBreadcrumb(): string
    {
        return 'Beüzemelési Naplók';
    }
}
