<?php

namespace App\Filament\Resources\ProductIntakeResource\Pages;

use App\Filament\Resources\ProductIntakeResource;
use App\Models\Product;
use App\Models\ProductIntakeItem;
use Filament\Resources\Pages\CreateRecord;

class CreateProductIntake extends CreateRecord
{
    protected static string $resource = ProductIntakeResource::class;
    public function getHeading(): string
    {
        return 'Új Bevételezés létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Bevételezés hozzáadása';
    }
}
