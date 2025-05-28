<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductOutputResource\Pages;
use App\Filament\Resources\ProductOutputResource\RelationManagers;
use App\Models\ProductOutput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use App\Forms\Schemas\CustomerFormSchema;
use App\Forms\Schemas\ProductOutputFormSchema;
use App\Models\Company;
use App\Models\User;
use App\Tables\Schemas\ProductOutputTableSchema;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Support\Colors\Color;
class ProductOutputResource extends Resource
{
    protected static ?string $model = ProductOutput::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-minus';
    protected static ?string $navigationGroup = 'Készletnyilvántartó';
    protected static ?string $pluralModelLabel = 'Kiadás';

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
               ...ProductOutputFormSchema::get()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...ProductOutputTableSchema::columns()
            ])
            ->filters([
                ...ProductOutputTableSchema::filters()
            ])
            ->actions([
                ...ProductOutputTableSchema::actions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Törlés'),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductOutputs::route('/'),
            'create' => Pages\CreateProductOutput::route('/create'),
            'view' => Pages\ViewProductOutput::route('/{record}'),
            'edit' => Pages\EditProductOutput::route('/{record}/edit'),
        ];
    }
}
