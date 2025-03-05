<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompany extends ViewRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Szerkesztés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Cég Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Cég Megtekintése';
    }
}
