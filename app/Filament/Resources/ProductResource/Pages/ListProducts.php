<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Termék'),
        ];
    }
    public function getHeading(): string
    {
        return 'Termékek';
    }
    public function getBreadcrumb(): string
    {
        return 'Termékek';
    }
}
