<?php

namespace App\Filament\Resources\ProductOutputResource\Pages;

use App\Filament\Resources\ProductOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListProductOutputs extends ListRecords
{
    protected static string $resource = ProductOutputResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Kiadás'),
        ];
    }
    public function getHeading(): string
    {
        return 'Kiadás';
    }
    public function getBreadcrumb(): string
    {
        return 'Kiadás';
    }
}
