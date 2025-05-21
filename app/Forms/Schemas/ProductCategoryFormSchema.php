<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
class ProductCategoryFormSchema
{
    public static function get(): array
    {
        return [
                Forms\Components\TextInput::make('name')
                    ->label('Elnevezés')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('short_description')
                    ->label('Rövid Leírás')
                    ->columnSpanFull(),
        ];
    }
}
