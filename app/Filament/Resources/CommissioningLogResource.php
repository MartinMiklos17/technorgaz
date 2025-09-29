<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CommissioningLog;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Forms\Schemas\CommissioningLogFormSchema;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CommissioningLogResource\Pages;
use App\Filament\Resources\CommissioningLogResource\RelationManagers;

class CommissioningLogResource extends Resource
{
    protected static ?string $model = CommissioningLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup='Naplók';
    protected static ?string $pluralModelLabel   = 'Beüzemelési Naplók';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...CommissioningLogFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('serial_number')
                ->label('Gyári szám')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_name')
                ->label('Vevő neve')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_zip')
                ->label('Irányítószám')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_city')
                ->label('Város')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_street')
                ->label('Utca')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_street_number')
                ->label('Házszám')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_email')
                ->label('Vevő e-mail')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_phone')
                ->label('Vevő telefon')
                ->searchable(),

            Tables\Columns\IconColumn::make('has_sludge_separator')
                ->label('Van iszapelválasztó')
                ->boolean(),

            Tables\Columns\TextColumn::make('product.name')
                ->label('Készülék típusa')
                ->numeric()   // ha nem szám, ezt vedd ki
                ->sortable(),

            Tables\Columns\TextColumn::make('burner_pressure')
                ->label('Égőnyomás')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('flue_gas_temperature')
                ->label('Füstgáz hőmérséklet')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('co2_value')
                ->label('CO₂ érték')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('co_value')
                ->label('CO érték')
                ->numeric()
                ->sortable(),

            Tables\Columns\IconColumn::make('has_eu_wind_grille')
                ->label('EU szélrács')
                ->boolean(),

            Tables\Columns\IconColumn::make('safety_devices_ok')
                ->label('Biztonsági elemek működnek')
                ->boolean(),

            Tables\Columns\IconColumn::make('flue_gas_backflow')
                ->label('Füstgáz visszaáramlás')
                ->boolean(),

            Tables\Columns\IconColumn::make('gas_tight')
                ->label('Gáz tömör')
                ->boolean(),

            Tables\Columns\TextColumn::make('water_pressure')
                ->label('Víznyomás')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('pdf_path')
                ->label('PDF útvonal')
                ->searchable(),

            Tables\Columns\TextColumn::make('created_by')
                ->label('Létrehozta (ID)')
                ->numeric()
                ->sortable(),

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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissioningLogs::route('/'),
            'create' => Pages\CreateCommissioningLog::route('/create'),
            'view' => Pages\ViewCommissioningLog::route('/{record}'),
            'edit' => Pages\EditCommissioningLog::route('/{record}/edit'),
        ];
    }
}
