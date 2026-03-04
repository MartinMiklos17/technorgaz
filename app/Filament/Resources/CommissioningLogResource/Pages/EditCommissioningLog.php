<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;
use Livewire\Attributes\On;

class EditCommissioningLog extends BaseEditRecord
{
    protected static string $resource = CommissioningLogResource::class;

    #[On('serialNumberScanned')]
    public function onSerialNumberScanned($value)
    {
        $this->data['serial_number'] = $value;

        $this->unmountFormComponentAction();
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
