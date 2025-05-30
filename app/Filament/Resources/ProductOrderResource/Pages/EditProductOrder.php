<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Filament\Resources\ProductOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductOrder extends EditRecord
{
    protected static string $resource = ProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
        ];
    }
    public function getHeading(): string
    {
        return 'Termék Rendelés szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék Rendelés szerkesztése';
    }
}
