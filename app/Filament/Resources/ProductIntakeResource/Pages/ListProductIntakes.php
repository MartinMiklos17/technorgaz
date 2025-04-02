<?php

namespace App\Filament\Resources\ProductIntakeResource\Pages;

use App\Filament\Resources\ProductIntakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
class ListProductIntakes extends ListRecords
{
    protected static string $resource = ProductIntakeResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Bevételezés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Bevételezés';
    }
    public function getBreadcrumb(): string
    {
        return 'Bevételezés';
    }
}
