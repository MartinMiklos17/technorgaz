<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\BaseCreateRecord;
class CreateCompany extends BaseCreateRecord
{
    protected static string $resource = CompanyResource::class;
    public function getHeading(): string
    {
        return 'Új Cég létrehozása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új cég hozzáadása';
    }
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Mentés')
                ->action('save')
                ->color('primary'),

            Actions\Action::make('cancel')
                ->label('Mégse')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
