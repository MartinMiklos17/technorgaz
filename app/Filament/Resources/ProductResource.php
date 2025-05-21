<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Form as FilamentForm;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Forms\Schemas\ProductFormSchema;
use App\Tables\Schemas\ProductTableSchema;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // Navigation & Labeling
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Termékek';
    protected static ?string $pluralModelLabel   = 'Termékek';
    protected static ?int $navigationSort=0;
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
                ...ProductFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...ProductTableSchema::columns()
            ])
            ->filters([
                ...ProductTableSchema::filters()
            ])
            ->actions([
                ...ProductTableSchema::actions()
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        // If you create any relation managers (e.g., for product photos as a separate table),
        // you can register them here.
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
