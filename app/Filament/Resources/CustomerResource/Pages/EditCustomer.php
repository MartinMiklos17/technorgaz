<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;

class EditCustomer extends BaseEditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
        ];
    }
    public function getHeading(): string
    {
        return 'Vevő szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Vevő szerkesztése';
    }
}
