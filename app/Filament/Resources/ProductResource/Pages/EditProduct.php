<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;

class EditProduct extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
        ];
    }
    public function getHeading(): string
    {
        return 'Termék szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék szerkesztése';
    }
}
