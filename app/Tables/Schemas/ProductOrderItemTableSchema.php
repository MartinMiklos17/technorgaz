<?php

namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ProductOrderItemTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('product.name')
                ->label('Termék')
                ->sortable()
                ->searchable(),

            TextColumn::make('sku')
                ->label('Cikkszám')
                ->sortable(),

            TextColumn::make('quantity')
                ->label('Mennyiség')
                ->sortable(),

            TextColumn::make('net_unit_price')
                ->label('Nettó egységár')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('net_total_price')
                ->label('Nettó összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('vat_amount')
                ->label('ÁFA összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('gross_unit_price')
                ->label('Bruttó egységár')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('gross_total_price')
                ->label('Bruttó összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('created_at')
                ->label('Létrehozva')
                ->dateTime('Y.m.d H:i')
                ->sortable(),
        ];
    }

    public static function filters(): array
    {
        return [
            // Például: szűrés termék szerint vagy idő szerint később hozzáadható
        ];
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make()->label('Részletek'),
            //Tables\Actions\EditAction::make()->label('Szerkesztés'),
        ];
    }

    public static function headerActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()->label('Új tétel hozzáadása'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make()->label('Tömeges törlés'),
        ];
    }
}
