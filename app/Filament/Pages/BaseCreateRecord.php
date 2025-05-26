<?php

namespace App\Filament\Pages;

use Filament\Resources\Pages\CreateRecord;

class BaseCreateRecord extends CreateRecord
{
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
