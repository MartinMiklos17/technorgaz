<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
class SupplierTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip')
                    ->label('Irányítószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Város')
                    ->searchable(),
                Tables\Columns\TextColumn::make('street')
                    ->label('Utca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('streetnumber')
                    ->label('Házszám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('Emelet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('door')
                    ->label('Ajtó')
                    ->searchable(),
                Tables\Columns\TextColumn::make('taxnum')
                    ->label('Adószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Kapcsolattartó neve')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email cím')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefonszám')
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
                ];
    }
    public static function filters(): array
    {
        return [
            SelectFilter::make('name')
                ->label('Név')
                ->options(fn () => Supplier::query()
                    ->select('name')
                    ->distinct()
                    ->pluck('name', 'name')
                    ->filter()
                    ->toArray())
                ->searchable(),

            SelectFilter::make('email')
                ->label('Email cím')
                ->options(fn () => Supplier::query()
                    ->select('email')
                    ->distinct()
                    ->pluck('email', 'email')
                    ->filter()
                    ->toArray())
                ->searchable(),

            SelectFilter::make('zip')
                ->label('Irányítószám')
                ->options(fn () => Supplier::query()
                    ->select('zip')
                    ->distinct()
                    ->pluck('zip', 'zip')
                    ->filter()
                    ->toArray())
                ->searchable(),

            Filter::make('city')
                ->label('Város')
                ->form([
                    TextInput::make('value')->label('Város'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('city', 'like', "%$value%")
                    )
                ),

            Filter::make('street')
                ->label('Utca')
                ->form([
                    TextInput::make('value')->label('Utca'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('street', 'like', "%$value%")
                    )
                ),

            Filter::make('streetnumber')
                ->label('Házszám')
                ->form([
                    TextInput::make('value')->label('Házszám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('streetnumber', 'like', "%$value%")
                    )
                ),

            Filter::make('floor')
                ->label('Emelet')
                ->form([
                    TextInput::make('value')->label('Emelet'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('floor', 'like', "%$value%")
                    )
                ),

            Filter::make('door')
                ->label('Ajtó')
                ->form([
                    TextInput::make('value')->label('Ajtó'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('door', 'like', "%$value%")
                    )
                ),

            Filter::make('taxnum')
                ->label('Adószám')
                ->form([
                    TextInput::make('value')->label('Adószám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('taxnum', 'like', "%$value%")
                    )
                ),

            Filter::make('contact_name')
                ->label('Kapcsolattartó')
                ->form([
                    TextInput::make('value')->label('Kapcsolattartó neve'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('contact_name', 'like', "%$value%")
                    )
                ),

            Filter::make('phone')
                ->label('Telefonszám')
                ->form([
                    TextInput::make('value')->label('Telefonszám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('phone', 'like', "%$value%")
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
