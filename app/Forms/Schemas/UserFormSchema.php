<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use App\Models\Company;
class UserFormSchema
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
                    ->native(false)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? AccountType::tryFrom($state) : $state)
                    ->formatStateUsing(fn ($state) => $state instanceof AccountType ? $state->value : $state)
                ]),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Név'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // A cégkapcsolat kiválasztása (feltételezve, hogy a user tábla 'company_id' kulcsot használ)
                Forms\Components\Select::make('company_id')
                ->label('Cég')
                ->required()
                ->options(Company::all()->pluck('company_name', 'id')->toArray())
                ->searchable(),

                // Admin szerepkör
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin Jogosultság?'),
        ];
    }
}
