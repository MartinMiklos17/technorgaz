<?php

namespace App\Filament\Resources\ProductOutputResource\Pages;

use App\Filament\Resources\ProductOutputResource;
use Filament\Actions;
use App\Filament\Pages\BaseEditRecord;

class EditProductOutput extends BaseEditRecord
{
    protected static string $resource = ProductOutputResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Vissza'),
            //delete action
            Actions\DeleteAction::make()->label('Törlés'),
        ];
    }
    public function getHeading(): string
    {
        return 'Kiadás szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Kiadás szerkesztése';
    }
}
