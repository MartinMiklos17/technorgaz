<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductIntakeResource\Pages;
use App\Models\Product;
use App\Models\ProductIntake;
use App\Models\ProductIntakeItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Forms\Components\ZipLookupField;
use App\Forms\Schemas\ProductIntakeFormSchema;
use App\Tables\Schemas\ProductIntakeTableSchema;

class ProductIntakeResource extends Resource
{
    protected static ?string $model = ProductIntake::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationGroup = 'Készletnyilvántartó';
    protected static ?string $pluralModelLabel = 'Bevételezés';

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
                ...ProductIntakeFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...ProductIntakeTableSchema::columns()
            ])
            ->actions([
                ...ProductIntakeTableSchema::actions()
            ])
            ->bulkActions([
                ...ProductIntakeTableSchema::bulkActions()
            ])
            ->filters([
                ...ProductIntakeTableSchema::filters()
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductIntakes::route('/'),
            'create' => Pages\CreateProductIntake::route('/create'),
            'view' => Pages\ViewProductIntake::route('/{record}'),
            'edit' => Pages\EditProductIntake::route('/{record}/edit'),
        ];
    }
}
