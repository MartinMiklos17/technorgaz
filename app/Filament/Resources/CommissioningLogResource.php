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
    protected static ?string $navigationGroup='Szervíznaplók';
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_zip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_street')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_street_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_sludge_separator')
                    ->boolean(),
                Tables\Columns\TextColumn::make('product.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('burner_pressure')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flue_gas_temperature')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('co2_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('co_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_eu_wind_grille')
                    ->boolean(),
                Tables\Columns\IconColumn::make('safety_devices_ok')
                    ->boolean(),
                Tables\Columns\IconColumn::make('flue_gas_backflow')
                    ->boolean(),
                Tables\Columns\IconColumn::make('gas_tight')
                    ->boolean(),
                Tables\Columns\TextColumn::make('water_pressure')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pdf_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
