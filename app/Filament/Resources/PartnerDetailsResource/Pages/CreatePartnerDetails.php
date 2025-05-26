<?php

namespace App\Filament\Resources\PartnerDetailsResource\Pages;

use App\Filament\Resources\PartnerDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\BaseCreateRecord;
class CreatePartnerDetails extends BaseCreateRecord
{
    protected static string $resource = PartnerDetailsResource::class;
    public function getHeading(): string
    {
        return 'Új Partner Adat létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Partner Adat hozzáadása';
    }
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Mentés')
                ->action('save')
                ->color('primary'),

            Actions\Action::make('cancel')
                ->label('Mégse')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
