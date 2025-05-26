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
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Beszállító')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label('Dátum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Tételek száma')
                    ->counts('items') // auto relationship count
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Összes érték (nettó)')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum(fn ($item) => $item->quantity * $item->unit_price);
                    })
                    ->money('HUF') // vagy 'EUR', ha úgy használod
                    ->sortable(),

                Tables\Columns\TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->note)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
