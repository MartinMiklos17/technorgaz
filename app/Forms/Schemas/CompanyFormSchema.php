<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use App\Models\User;
class CompanyFormSchema
{
    public static function get(): array
    {
        return [
                Forms\Components\Select::make('user_id')
                    ->label('Felhasználó')
                    ->required()
                    ->options(User::all()->pluck('name', 'id')->toArray())
                    ->searchable(),
                Forms\Components\TextInput::make('company_name')
                    ->label('Cég neve')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_country')
                    ->label('Ország')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_zip')
                    ->label('Irányítószám')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_city')
                    ->label('Város')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_address')
                    ->label('Cím')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_taxnum')
                    ->label('Adószám')
                    ->mask('99999999-9-99')
                    ->required()
                    ->maxLength(255),
        ];
    }
}
