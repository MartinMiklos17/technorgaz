<?php

namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Layout;
use Filament\Forms\Components\DatePicker;

class ProductOrderTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('order_date')
                ->label('Dátum')
                ->date('Y.m.d')
                ->sortable(),

            TextColumn::make('note')
                ->label('Megjegyzés')
                ->limit(50)
                ->wrap(),

            IconColumn::make('is_sent')
                ->label('Elküldve')
                ->boolean(),

            TextColumn::make('total_quantity')
                ->label('Összes darab')
                ->sortable(),
            TextColumn::make('total_net_amount')
                ->label('Nettó összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('total_vat_amount')
                ->label('ÁFA összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('total_gross_amount')
                ->label('Bruttó összesen')
                ->money('HUF', locale: 'hu'),

            TextColumn::make('created_at')
                ->label('Létrehozva')
                ->since()
                ->sortable(),
        ];
    }

    public static function filters(): array
    {
        return [
            Filter::make('date')
                ->form([
                    DatePicker::make('from')->label('Dátumtól')->native(false),
                    DatePicker::make('until')->label('Dátumig')->native(false),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                }),
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
            Tables\Actions\CreateAction::make()->label('Új rendelés'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make()->label('Tömeges törlés'),
        ];
    }
}
