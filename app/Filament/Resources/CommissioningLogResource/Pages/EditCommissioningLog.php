<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditCommissioningLog extends EditRecord
{
    protected static string $resource = CommissioningLogResource::class;

    #[On('serialNumberScanned')]
    public function onSerialNumberScanned($value)
    {
        $this->data['serial_number'] = $value;
    }

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
