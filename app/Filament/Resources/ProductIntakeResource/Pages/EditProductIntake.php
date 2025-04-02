<?php

namespace App\Filament\Resources\ProductIntakeResource\Pages;

use App\Filament\Resources\ProductIntakeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Product;
class EditProductIntake extends EditRecord
{
    protected static string $resource = ProductIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
        ];
    }
    public function getHeading(): string
    {
        return 'Bevételezés szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Bevételezés szerkesztése';
    }
}
