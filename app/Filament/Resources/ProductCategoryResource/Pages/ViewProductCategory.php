<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductCategory extends ViewRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label("Szerkesztés"),
        ];
    }
    public function getHeading(): string
    {
        return 'Termék Kategória Megtekintése';
    }
    public function getBreadcrumb(): string
    {
        return 'Termék Kategória Megtekintése';
    }
}
