<?php

namespace App\Filament\Resources\ProductIntakeResource\Pages;

use App\Filament\Resources\ProductIntakeResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;
use App\Models\Product;
class EditProductIntake extends BaseEditRecord
{
    protected static string $resource = ProductIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
            Actions\DeleteAction::make()->label('Törlés'),
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
