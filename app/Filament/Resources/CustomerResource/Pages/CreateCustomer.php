<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
    public function getHeading(): string
    {
        return 'Új Vevő létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Vevő hozzáadása';
    }
}
