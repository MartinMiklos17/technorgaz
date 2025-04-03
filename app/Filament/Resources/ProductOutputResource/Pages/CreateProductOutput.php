<?php

namespace App\Filament\Resources\ProductOutputResource\Pages;

use App\Filament\Resources\ProductOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductOutput extends CreateRecord
{
    protected static string $resource = ProductOutputResource::class;
    public function getHeading(): string
    {
        return 'Új Bevételezés létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Bevételezés hozzáadása';
    }
}
