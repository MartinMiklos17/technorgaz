<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
class SupplierFormSchema
{
    public static function get(): array
    {
        return [
                Forms\Components\Section::make('Kontakt adatok')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Név')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('taxnum')
                            ->label('Adószám')
                            ->maxLength(100)
                            ->default(null)
                            ->mask('99999999-9-99')
                            ->rule('regex:/^\d{8}-\d-\d{2}$/'),
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Kapcsolattartó neve')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefonszám')
                            ->tel()
                            ->maxLength(50)
                            ->default(null),
                    ]),
                Forms\Components\Section::make('Cím')
                    ->schema([
                        Forms\Components\TextInput::make('zip')
                            ->label('Irsz')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('city')
                            ->label('Város')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('street')
                            ->label('Utca')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('streetnumber')
                            ->label('Házszám')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('floor')
                            ->label('Emelet')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('door')
                            ->label('Ajtó')
                            ->maxLength(50),
                    ]),
        ];
    }
}
