<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Vevő'),
        ];
    }
    public function getHeading(): string
    {
        return 'Vevők';
    }
    public function getBreadcrumb(): string
    {
        return 'Vevők';
    }
}
