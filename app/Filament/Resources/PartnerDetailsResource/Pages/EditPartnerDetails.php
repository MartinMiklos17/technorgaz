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
            Actions\ViewAction::make()->label('Vissza'),
            Actions\DeleteAction::make()->label('Törlés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Partner Adatok szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Partner Adatok szerkesztése';
    }
    /*protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Mentés')
                ->action('save')
                ->color('primary')
                ->extraAttributes([
                    'wire:loading.attr' => 'disabled',
                    'wire:target' => '
                        data.gas_installer_license_front_image,
                        data.gas_installer_license_back_image,
                        data.flue_gas_analyzer_doc_image
                    ',
                ]),

            Actions\Action::make('cancel')
                ->label('Mégse')
                ->url($this->getResource()::getUrl('index')),
        ];
    }*/
}
