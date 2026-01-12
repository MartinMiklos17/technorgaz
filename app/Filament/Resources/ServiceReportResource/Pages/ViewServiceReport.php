<?php

namespace App\Filament\Resources\ServiceReportResource\Pages;

use App\Filament\Resources\ServiceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceReport extends ViewRecord
{
    protected static string $resource = ServiceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    public function getHeading(): string
    {
        return 'Szervíznapló Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Szervíznapló Megtekitése';
    }
}
