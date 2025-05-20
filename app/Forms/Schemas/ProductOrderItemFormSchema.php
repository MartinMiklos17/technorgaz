<?php

namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Models\Product;

class ProductOrderItemFormSchema
{
    public static function get(): array
    {
        return [
            Section::make('Rendelési tétel')
                ->schema([
                    Select::make('product_id')
                        ->label('Termék')
                        ->options(Product::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Mennyiség')
                        ->numeric()
                        ->required(),

                    TextInput::make('net_unit_price')
                        ->label('Nettó egységár (Ft)')
                        ->numeric()
                        ->required(),

                    TextInput::make('net_total_price')
                        ->label('Nettó összesen')
                        ->numeric()
                        ->readOnly(),

                    TextInput::make('gross_unit_price')
                        ->label('Bruttó egységár')
                        ->numeric()
                        ->readOnly(),

                    TextInput::make('gross_total_price')
                        ->label('Bruttó összesen')
                        ->numeric()
                        ->readOnly(),

                    TextInput::make('vat_amount')
                        ->label('ÁFA összesen')
                        ->numeric()
                        ->readOnly(),
                ])
                ->columns(2),
        ];
    }
}
