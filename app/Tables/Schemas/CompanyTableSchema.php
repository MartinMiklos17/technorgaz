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

class CompanyTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('company_name')
                ->label('Cég neve')
                ->searchable(),
            TextColumn::make('company_country')
                ->label('Ország')
                ->searchable(),
            TextColumn::make('company_zip')
                ->label('Irányítószám')
                ->searchable(),
            TextColumn::make('company_city')
                ->label('Város')
                ->searchable(),
            TextColumn::make('company_address')
                ->label('Cím')
                ->searchable(),
            TextColumn::make('company_taxnum')
                ->label('Adószám')
                ->searchable(),
            TextColumn::make('created_at')
                ->label('Létrehozva')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Módosítva')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function filters(): array
    {
        return [
        Tables\Filters\SelectFilter::make('company_name')
            ->label('Cég neve')
            ->options(fn () => \App\Models\Company::query()
                ->distinct()
                ->pluck('company_name', 'company_name')
                ->filter()
                ->toArray()
            )
            ->searchable(),

            Tables\Filters\Filter::make('company_country')
                ->form([
                    TextInput::make('value')->label('Ország'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('company_country', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('company_zip')
                ->form([
                    TextInput::make('value')->label('Irányítószám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('company_zip', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('company_city')
                ->form([
                    TextInput::make('value')->label('Város'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('company_city', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('company_address')
                ->form([
                    TextInput::make('value')->label('Cím'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('company_address', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('company_taxnum')
                ->form([
                    TextInput::make('value')->label('Adószám'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('company_taxnum', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('created_at')
                ->form([
                    DatePicker::make('value')->label('Létrehozva ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('created_at', '>=', $value)
                    )
                ),

            Tables\Filters\Filter::make('updated_at')
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
