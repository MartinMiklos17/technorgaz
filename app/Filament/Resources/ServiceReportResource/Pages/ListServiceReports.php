<?php

namespace App\Filament\Resources\ServiceReportResource\Pages;

use App\Filament\Resources\ServiceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListServiceReports extends ListRecords
{
    protected static string $resource = ServiceReportResource::class;

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
            Actions\CreateAction::make()->label("Új Szervíznapló"),
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
        return 'Szervíznaplók';
    }

    public function getBreadcrumb(): string
    {
        return 'Szervíznaplók';
    }
}
