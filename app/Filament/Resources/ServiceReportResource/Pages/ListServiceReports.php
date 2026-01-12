<?php

namespace App\Filament\Resources\ServiceReportResource\Pages;

use App\Filament\Resources\ServiceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceReports extends ListRecords
{
    protected static string $resource = ServiceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label("Új Szervíznapló"),
        ];
    }
    public function getHeading(): string
    {
        return 'Szervíznaplók';
    }
    public function getBreadcrumb(): string
    {
        return 'Szervíznaplók';
    }
}
