<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductOrderResource\Pages;
use App\Filament\Resources\ProductOrderResource\RelationManagers;
use App\Models\ProductOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Forms\Schemas\ProductOrderFormSchema;
use App\Tables\Schemas\ProductOrderTableSchema;
use App\Filament\Resources\ProductOrderResource\RelationManagers\ProductOrderItemRelationManager;

class ProductOrderResource extends Resource
{
    protected static ?string $model = ProductOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Termékek';
    protected static ?string $pluralModelLabel   = 'Termék Rendelések';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...ProductOrderFormSchema::get(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...ProductOrderTableSchema::columns(),
            ])
            ->filters([
                ...ProductOrderTableSchema::filters(),
            ])
            ->actions([
                ...ProductOrderTableSchema::actions(),
            ])
            ->bulkActions([
                ...ProductOrderTableSchema::bulkActions(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductOrderItemRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductOrders::route('/'),
            'create' => Pages\CreateProductOrder::route('/create'),
            'view' => Pages\ViewProductOrder::route('/{record}'),
            //'edit' => Pages\EditProductOrder::route('/{record}/edit'),
        ];
    }
}
