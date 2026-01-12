<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceReportResource\Pages;
use App\Filament\Resources\ServiceReportResource\RelationManagers;
use App\Models\ServiceReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Tables\Schemas\ServiceReportTableSchema;
use App\Forms\Schemas\ServiceReportFormSchema;
class ServiceReportResource extends Resource
{
    protected static ?string $model = ServiceReport::class;
    protected static ?string $navigationGroup='Naplók';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $pluralModelLabel   = 'Szervíz Naplók';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([...ServiceReportFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([...ServiceReportTableSchema::columns()
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceReports::route('/'),
            'create' => Pages\CreateServiceReport::route('/create'),
            'view' => Pages\ViewServiceReport::route('/{record}'),
            'edit' => Pages\EditServiceReport::route('/{record}/edit'),
        ];
    }
}
