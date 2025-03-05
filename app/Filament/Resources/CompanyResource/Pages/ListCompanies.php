<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Új Cég'),
        ];
    }
    public function getHeading(): string
    {
        return 'Cégek';
    }
    public function getBreadcrumb(): string
    {
        return 'Cégek';
    }
}
