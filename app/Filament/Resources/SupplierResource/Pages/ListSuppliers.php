<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Beszállító'),
        ];
    }
    public function getHeading(): string
    {
        return 'Beszállítók';
    }
    public function getBreadcrumb(): string
    {
        return 'Beszállítók';
    }
}
