<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Forms\Components\MapboxField;
use App\Models\Company;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use PhpParser\Node\Stmt\Label;

class PartnerDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'PartnerDetails';
    protected static ?string $title = 'Partner Adatok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Felhasználó')
                    ->required()
                    ->options(User::all()->pluck('name', 'id')->toArray())
                    ->searchable(),

                Select::make('company_id')
                    ->label('Cég')
                    ->required()
                    ->options(Company::all()->pluck('company_name', 'id')->toArray())
                    ->searchable(),

                Toggle::make('client_take')
                    ->label('Ügyeletet vállal?')
                    ->required(),

                Toggle::make('complete_execution')
                    ->label('Teljes kivitelezés')
                    ->required(),

                TextInput::make('gas_installer_license')
                    ->label('Gázszerelő engedély')
                    ->maxLength(255)
                    ->default(null),

                DatePicker::make('license_expiration')
                    ->label('Engedély lejárata'),

                TextInput::make('contact_person')
                    ->label('Kapcsolattartó')
                    ->maxLength(255)
                    ->default(null),

                TextInput::make('phone')
                    ->label('Telefonszám')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),

                TextInput::make('location_address')
                    ->label('Cím')
                    ->live()
                    ->readOnly(),

                TextInput::make('latitude')
                    ->label('Szélesség')
                    ->live()
                    ->readOnly(),

                TextInput::make('longitude')
                    ->label('Hosszúság')
                    ->live()
                    ->readOnly(),

                MapboxField::make('map')
                    ->label('Térkép'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Felhasználó')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.company_name')
                    ->label('Cég')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('client_take')
                    ->label('Ügyeletet vállal?')
                    ->boolean(),

                Tables\Columns\IconColumn::make('complete_execution')
                    ->label('Teljes kivitelezés')
                    ->boolean(),

                Tables\Columns\TextColumn::make('gas_installer_license')
                    ->label('Gázszerelő engedély')
                    ->searchable(),

                Tables\Columns\TextColumn::make('license_expiration')
                    ->label('Engedély lejárata')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Kapcsolattartó')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefonszám')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location_address')
                    ->label('Cím')
                    ->searchable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Szélesség')
                    ->sortable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('Hosszúság')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Frissítve')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Ide kerülhetnek a szűrők, ha szükséges
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Új Partner Adatok')
                ->visible(fn (\Filament\Resources\RelationManagers\RelationManager $livewire) =>
                    ! $livewire->getRelationship()->exists()
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->Label('Szerkesztés')
                ->modalSubmitActionLabel('Mentés')
                ->modalHeading('Partner Adatok szerkesztése')
                ->modalcancelActionLabel('Mégse'),
                Tables\Actions\DeleteAction::make()->label('Törlés')
                ->modalSubmitActionLabel('Mentés')
                ->modalHeading('Partner Adatok Törlése')
                ->modalDescription('Biztosan törölni szeretné a kiválasztott partnereket?')
                ->modalcancelActionLabel('Mégse'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Kijelöltek Törlése')
                    ->modalSubmitActionLabel('Mentés')
                    ->modalHeading('Partner Adatok Törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott Partner Adatokat?')
                    ->modalcancelActionLabel('Mégse'),
                ])->label('Törlés'),
            ]);
    }
}
