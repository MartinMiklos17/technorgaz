<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CommissioningLog;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Forms\Schemas\CommissioningLogFormSchema;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CommissioningLogResource\Pages;
use App\Filament\Resources\CommissioningLogResource\RelationManagers;
use App\Tables\Schemas\CommissioningLogTableSchema;

class CommissioningLogResource extends Resource
{
    protected static ?string $model = CommissioningLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup='Naplók';
    protected static ?string $pluralModelLabel   = 'Beüzemelési Naplók';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...CommissioningLogFormSchema::get()
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'creator'])
            ->visibleTo(auth()->user());  // <-- a scope használata
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...CommissioningLogTableSchema::columns()
            ])

            ->filters([
                ...CommissioningLogTableSchema::filters()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(fn () => auth()->user()?->isAdmin()),
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
            'index' => Pages\ListCommissioningLogs::route('/'),
            'create' => Pages\CreateCommissioningLog::route('/create'),
            //'view' => Pages\ViewCommissioningLog::route('/{record}'),
            'edit' => Pages\EditCommissioningLog::route('/{record}/edit'),
        ];
    }
}
