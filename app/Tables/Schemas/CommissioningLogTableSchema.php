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
use App\Models\Product;

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
                ->state(fn ($record) => 'Megnyitás')
                ->url(fn ($record) => route('commissioning-logs.pdf', $record))
                ->openUrlInNewTab()
                ->sortable(false)
                ->icon('heroicon-o-eye')
                ->badge(),

            Tables\Columns\TextColumn::make('pdf_download')
                ->label('PDF letöltés')
                ->state(fn ($record) => 'Letöltés')
                ->url(fn ($record) => route('commissioning-logs.pdf.download', $record))
                ->sortable(false)
                ->icon('heroicon-o-arrow-down-tray')
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('serial_number')
                ->label('Gyári szám')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_name')
                ->label('Vevő neve')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_zip')
                ->label('Irányítószám')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_city')
                ->label('Város')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_street')
                ->label('Utca')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_street_number')
                ->label('Házszám')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_email')
                ->label('Vevő e-mail')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_phone')
                ->label('Vevő telefon')
                ->searchable()
                ->sortable(),

            Tables\Columns\IconColumn::make('has_sludge_separator')
                ->label('Van iszapelválasztó')
                ->boolean()
                ->sortable(),

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
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('safety_devices_ok')
                ->label('Biztonsági elemek működnek')
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('flue_gas_backflow')
                ->label('Füstgáz visszaáramlás')
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('gas_tight')
                ->label('Gáz tömör')
                ->boolean()
                ->sortable(),

            Tables\Columns\TextColumn::make('water_pressure')
                ->label('Víznyomás')
                ->numeric()
                ->sortable(),

            Tables\Columns\IconColumn::make('correct_phase_connection')
                ->label('Fázis helyes bekötés')
                ->boolean()
                ->sortable(),

            Tables\Columns\TextColumn::make('notes')
                ->label('Megjegyzés')
                ->limit(40)
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

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
            SelectFilter::make('created_by')
                ->label('Létrehozta')
                ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            SelectFilter::make('product_id')
                ->label('Készülék típusa')
                ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            Filter::make('serial_number')
                ->label('Gyári szám')
                ->form([
                    TextInput::make('value')->label('Gyári szám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('serial_number', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_name')
                ->label('Vevő neve')
                ->form([
                    TextInput::make('value')->label('Vevő neve'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_name', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_email')
                ->label('Vevő e-mail')
                ->form([
                    TextInput::make('value')->label('Vevő e-mail'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_email', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_phone')
                ->label('Vevő telefon')
                ->form([
                    TextInput::make('value')->label('Vevő telefon'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_phone', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_zip')
                ->label('Irányítószám')
                ->form([
                    TextInput::make('value')->label('Irányítószám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_zip', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_city')
                ->label('Város')
                ->form([
                    TextInput::make('value')->label('Város'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_city', 'like', "%$value%")
                    )
                ),

            Filter::make('customer_street')
                ->label('Utca')
                ->form([
                    TextInput::make('value')->label('Utca'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('customer_street', 'like', "%$value%")
                    )
                ),

            Filter::make('created_at')
                ->label('Létrehozva ettől')
                ->form([
                    DatePicker::make('value')->label('Létrehozva ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('created_at', '>=', $value)
                    )
                ),

            Filter::make('updated_at')
                ->label('Módosítva ettől')
                ->form([
                    DatePicker::make('value')->label('Módosítva ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('updated_at', '>=', $value)
                    )
                ),
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
