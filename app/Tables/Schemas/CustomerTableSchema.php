<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use App\Enums\AccountType;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
class CustomerTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('account_type')
                ->label('Fiók típusa')
                ->formatStateUsing(fn ($state) => AccountType::tryFrom($state)?->label() ?? '-'),

                Tables\Columns\TextColumn::make('billing_name')->label("Számlázási Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_address')->label("Számlázási Cím")
                    ->label('Számlázási cím')
                    ->getStateUsing(fn ($record) => "{$record->billing_zip} {$record->billing_city}, {$record->billing_street} {$record->billing_streetnumber}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_name')->label("Szállítási Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_address')->label("Szállítási Cím")
                    ->label('Szállítási cím')
                    ->getStateUsing(fn ($record) => "{$record->postal_zip} {$record->postal_city}, {$record->postal_street} {$record->postal_streetnumber}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('taxnumber')->label("Adószám")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')->label("Kontakt Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_email')->label("Email")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')->label("Telefonszám")
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ];
    }
    public static function filters(): array
    {
        return [
            SelectFilter::make('billing_name')
                ->label('Számlázási név')
                ->options(fn () => Customer::query()
                    ->select('billing_name')
                    ->distinct()
                    ->pluck('billing_name', 'billing_name')
                    ->filter()
                    ->toArray())
                ->searchable(),

            SelectFilter::make('contact_email')
                ->label('Email')
                ->options(fn () => Customer::query()
                    ->select('contact_email')
                    ->distinct()
                    ->pluck('contact_email', 'contact_email')
                    ->filter()
                    ->toArray())
                ->searchable(),

            Filter::make('postal_name')
                ->label('Szállítási név')
                ->form([
                    TextInput::make('value')->label('Szállítási név'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('postal_name', 'like', "%$value%")
                    )
                ),

            Filter::make('taxnumber')
                ->label('Adószám')
                ->form([
                    TextInput::make('value')->label('Adószám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('taxnumber', 'like', "%$value%")
                    )
                ),

            Filter::make('contact_name')
                ->label('Kapcsolattartó név')
                ->form([
                    TextInput::make('value')->label('Kapcsolattartó név'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('contact_name', 'like', "%$value%")
                    )
                ),

            Filter::make('contact_phone')
                ->label('Telefonszám')
                ->form([
                    TextInput::make('value')->label('Telefonszám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('contact_phone', 'like', "%$value%")
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
        return [
        ];
    }
    public static function bulkActions(): array
    {
        return [
        ];
    }
}
