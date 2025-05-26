<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\BaseCreateRecord;
class CreateProduct extends BaseCreateRecord
{
    protected static string $resource = ProductResource::class;
    public function getHeading(): string
    {
        return 'Új Termék létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Termék hozzáadása';
    }
}
