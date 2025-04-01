<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class CompanyRelationManager extends RelationManager
{
    protected static string $relationship = 'Company';
    protected static ?string $title = 'Cég Adatok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Cégnév')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_country')
                    ->label('Ország')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_zip')
                    ->label('Irszám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_city')
                    ->label('Város')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_address')
                    ->label('Cím')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_taxnum')
                    ->label('Adószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Módosítva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Új Cég')
                ->visible(fn (\Filament\Resources\RelationManagers\RelationManager $livewire) =>
                    ! $livewire->getRelationship()->exists()
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Szerkesztés')
                ->modalSubmitActionLabel('Mentés')
                ->modalHeading('Cég szerkesztése')
                ->modalcancelActionLabel('Mégse'),
                Tables\Actions\DeleteAction::make()->label('Törlés')
                ->modalHeading('Kapcsolt Cég Törlése')
                ->modalDescription('Biztosan törölni szeretné a kiválasztott Cég Adatokat?')
                ->modalcancelActionLabel('Mégse'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Kijelöltek Törlése')
                    ->modalHeading('Kapcsolt Cég Törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott Cég Adatokat?')
                    ->modalcancelActionLabel('Mégse'),
                ])->label('Törlés') ,
            ]);
    }
}
