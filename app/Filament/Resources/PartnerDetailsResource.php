<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerDetailsResource\Pages;
use App\Models\PartnerDetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Company;
use App\Models\User;
use App\Helpers\MapboxHelper;
use App\Forms\Components\MapboxField; // Az egyedi mező importálása
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Forms\Components\Livewire;
use App\Livewire\Foo;

class PartnerDetailsResource extends Resource
{
    protected static ?string $model = PartnerDetails::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Partnercégek';
    protected static ?string $pluralModelLabel   = 'Partner Adatok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->options(User::all()->pluck('name', 'id')->toArray())
                    ->searchable(),

                Forms\Components\Select::make('company_id')
                    ->required()
                    ->options(Company::all()->pluck('company_name', 'id')->toArray())
                    ->searchable(),

                Forms\Components\Toggle::make('client_take')->required(),
                Forms\Components\Toggle::make('complete_execution')->required(),

                Forms\Components\TextInput::make('gas_installer_license')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\DatePicker::make('license_expiration'),

                Forms\Components\TextInput::make('contact_person')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\TextInput::make('location_address')
                    ->id('data.location_address')
                    ->label(__('Cím'))
                    ->live()
                    ->readOnly(),
                Forms\Components\TextInput::make('latitude')
                ->id('data.latitude')
                    ->label(__('Szélesség'))
                    ->live()
                    ->readOnly(),
                Forms\Components\TextInput::make('longitude')
                ->id('data.longitude')
                    ->label(__('Hosszúság'))
                    ->live()
                    ->readOnly(),
                MapboxField::make('map')
                    ->label(__('Térkép')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Felhasználó'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.company_name')
                    ->label(__('Cég'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('client_take')
                    ->label(__('Ügyfél fogadása'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('complete_execution')
                    ->label(__('Teljes kivitelezés'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('gas_installer_license')
                    ->label(__('Gázszerelő engedély'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('license_expiration')
                    ->label(__('Engedély lejárata'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label(__('Kapcsolattartó'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Telefonszám'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('location_address')
                    ->label(__('Cím'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('Szélesség'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('Hosszúság'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Létrehozva'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Frissítve'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartnerDetails::route('/'),
            'create' => Pages\CreatePartnerDetails::route('/create'),
            'view' => Pages\ViewPartnerDetails::route('/{record}'),
            'edit' => Pages\EditPartnerDetails::route('/{record}/edit'),
        ];
    }
}
