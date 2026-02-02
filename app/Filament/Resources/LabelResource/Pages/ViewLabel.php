<?php

namespace App\Filament\Resources\LabelResource\Pages;

use App\Filament\Resources\LabelResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewLabel extends ViewRecord
{
    protected static string $resource = LabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cimke')
                ->label('Címke')
                ->icon('heroicon-o-tag')
                ->url(fn () => route('admin.label.get_img_1', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('adattabla')
                ->label('Adattábla')
                ->icon('heroicon-o-table-cells')
                ->url(fn () => route('admin.label.get_img_2', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('adatlap_pdf')
                ->label('Adatlap (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.label.get_pdf', $this->record))
                ->openUrlInNewTab(),

            Actions\EditAction::make(), // ha kell “Szerkesztés” gomb is
        ];
    }
}
