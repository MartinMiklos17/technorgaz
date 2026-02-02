<?php

namespace App\Filament\Resources\LabelResource\Pages;

use Filament\Actions;
use App\Filament\Resources\LabelResource;
use Filament\Resources\Pages\ListRecords;

class ListLabels extends ListRecords
{
    protected static string $resource = LabelResource::class;
        protected function getHeaderActions(): array
        {
            return [
                Actions\CreateAction::make()->label('Új Címke'),
            ];
        }
}
