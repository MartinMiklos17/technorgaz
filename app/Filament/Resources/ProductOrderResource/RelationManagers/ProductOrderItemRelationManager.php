<?php

namespace App\Filament\Resources\ProductOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Forms\Schemas\ProductOrderItemFormSchema;
use App\Tables\Schemas\ProductOrderItemTableSchema;

class ProductOrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...ProductOrderItemFormSchema::get(),
            ]);
    }
    public function canCreate(): bool
    {
        return false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                ...ProductOrderItemTableSchema::columns(),
            ])
            ->filters([
                ...ProductOrderItemTableSchema::filters(),
            ])
            ->headerActions([
                ...ProductOrderItemTableSchema::headerActions(),
            ])
            ->actions([
                ...ProductOrderItemTableSchema::actions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...ProductOrderItemTableSchema::bulkActions(),
                ]),
            ]);
    }
}
