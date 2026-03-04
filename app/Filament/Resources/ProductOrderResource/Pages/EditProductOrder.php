<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Filament\Resources\ProductOrderResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;

class EditProductOrder extends BaseEditRecord
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
