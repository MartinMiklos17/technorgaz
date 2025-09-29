<?php

namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TextFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class CommissioningLogTableSchema
{
    public static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('creator.name')
                ->label('Létrehozta')
                ->description(fn ($record) => $record->creator?->email) // apró extra: email a név alatt
                ->searchable(['creator.name', 'creator.email'])
                ->sortable(query: function (Builder $query, string $direction) {
                    $query->orderBy(
                        User::select('name')
                            ->whereColumn('users.id', 'commissioning_logs.created_by'),
                        $direction
                    );
                }),
            Tables\Columns\TextColumn::make('pdf_preview')
                ->label('Beüzemelési napló (PDF)')
                ->state(fn ($record) => $record->pdf_path ? 'Megnyitás' : '—')
                ->url(fn ($record) => $record->pdf_path ? route('commissioning-logs.pdf', $record) : null)
                ->openUrlInNewTab()
                ->sortable(false)
                ->icon('heroicon-o-eye')
                ->badge(),

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
        ];
    }

    public static function filters(): array
    {
        return [
        ];
    }


    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make()->label('Részletek'),
            Tables\Actions\EditAction::make()->label('Szerkesztés'),
        ];
    }

    public static function headerActions(): array
    {
        return [];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Törlés')                  ->modalSubmitActionLabel('Mentés')
                    ->modalHeading('Partner Adatok Törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott Céget?')
                    ->modalcancelActionLabel('Mégse'),
                ])->label('Törlés')
        ];
    }
}
