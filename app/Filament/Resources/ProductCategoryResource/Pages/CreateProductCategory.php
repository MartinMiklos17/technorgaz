<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

    public function getHeading(): string
    {
        return 'Új Termék Kategória létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Termék Kategória hozzáadása';
    }
}
