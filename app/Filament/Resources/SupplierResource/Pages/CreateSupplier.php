<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\BaseCreateRecord;
class CreateSupplier extends BaseCreateRecord
{
    protected static string $resource = SupplierResource::class;
    public function getHeading(): string
    {
        return 'Új Beszállító létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Beszállító hozzáadása';
    }
}
