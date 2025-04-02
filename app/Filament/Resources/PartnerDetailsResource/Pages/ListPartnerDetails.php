<?php

namespace App\Filament\Resources\PartnerDetailsResource\Pages;

use App\Filament\Resources\PartnerDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
class ListPartnerDetails extends ListRecords
{
    protected static string $resource = PartnerDetailsResource::class;
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
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
