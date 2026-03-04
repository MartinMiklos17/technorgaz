<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;

class EditProductCategory extends BaseEditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
            Actions\DeleteAction::make()->label('Törlés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Termék Kategória szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék Kategória szerkesztése';
    }
}
