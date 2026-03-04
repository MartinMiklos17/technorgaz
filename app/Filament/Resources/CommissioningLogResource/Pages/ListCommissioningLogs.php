<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use App\Filament\Resources\CommissioningLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListCommissioningLogs extends ListRecords
{
    protected static string $resource = CommissioningLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scan')
                ->label('Beolvasás')
                ->icon('heroicon-m-qr-code')
                ->modalContent(view('filament.components.qr-scanner'))
                ->modalHeading('Kód beolvasása')
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            Actions\CreateAction::make()->label("Új Beüzemelési Napló"),
        ];
    }

    #[On('serialNumberScanned')]
    public function onSerialNumberScanned(string $value): void
    {
        $this->tableSearch = $value;
        $this->resetPage();
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
