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
        return 'Új Kiadás létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Kiadás hozzáadása';
    }
}
