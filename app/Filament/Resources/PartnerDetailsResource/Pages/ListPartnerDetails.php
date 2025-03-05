<?php

namespace App\Filament\Resources\PartnerDetailsResource\Pages;

use App\Filament\Resources\PartnerDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartnerDetails extends ListRecords
{
    protected static string $resource = PartnerDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Partner'),
        ];
    }
    public function getHeading(): string
    {
        return 'Partner Adatok';
    }
    public function getBreadcrumb(): string
    {
        return 'Partner Adatok';
    }
}
