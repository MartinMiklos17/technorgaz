<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
class CustomerFormSchema
{
    public static function get(): array
    {
        return [
                Section::make('Partner típus')
                ->schema([
                    Forms\Components\Select::make('account_type')
                    ->label('Fiók típusa')
                    ->options(AccountType::options())
                    ->required()
                    ->native(false),
                ]),
                Section::make('Számlázási Adatok')
                ->schema([
                Forms\Components\TextInput::make('billing_name')->label("Számlázási Név")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_zip')->label("Számlázási Irányítószám")
                    ->required()
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('billing_city')->label("Számlázási Város")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_street')->label("Számlázási Utca")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_streetnumber')->label("Számlázási Házszám")
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('billing_floor')->label("Számlázási Emelet")
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('billing_door')->label("Számlázási Ajtó")
                    ->maxLength(50)
                    ->default(null),
                ]),
                Section::make('Szállítási Adatok')
                ->schema([
                Forms\Components\Toggle::make('postal_same_as_main')
                    ->label('Szállítási adatok megegyeznek az alap címmel')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $set('postal_name', $get('billing_name'));
                            $set('postal_zip', $get('billing_zip'));
                            $set('postal_city', $get('billing_city'));
                            $set('postal_street', $get('billing_street'));
                            $set('postal_streetnumber', $get('billing_streetnumber'));
                            $set('postal_floor', $get('billing_floor'));
                            $set('postal_door', $get('billing_door'));
                        }
                    }),
                Forms\Components\TextInput::make('postal_name')->label("Szállítási Név")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_zip')->label("Szállítási Irányítószám")
                    ->required()
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('postal_city')->label("Szállítási Város")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_street')->label("Szállítási Utca")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_streetnumber')->label("Szállítási Házszám")
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('postal_floor')->label("Szállítási Emelet")
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('postal_door')->label("Szállítási Ajtó")
                    ->maxLength(50)
                    ->default(null),
                ]),
                Section::make('További Adatok')
                ->schema([
                Forms\Components\TextInput::make('taxnumber')->label("Adószám")
                    ->mask('99999999-9-99')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('contact_name')->label("Kontakt Név")
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('contact_email')->label("Email")
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('contact_phone')->label("Telefonszám")
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                ]),
        ];
    }
}
