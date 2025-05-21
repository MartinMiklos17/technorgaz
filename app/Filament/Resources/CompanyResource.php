<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CompanyResource\RelationManagers\PartnerDetailsRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\UsersRelationManager;
use App\Forms\Schemas\CompanyFormSchema;
use App\Tables\Schemas\CompanyTableSchema;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup='Partnercégek';
    protected static ?string $pluralModelLabel = 'Cégek';
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
                ...CompanyFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...CompanyTableSchema::columns()
            ])
            ->filters([
                ...CompanyTableSchema::filters()
            ])
            ->actions([
                ...CompanyTableSchema::actions()
            ])
            ->bulkActions([
                ...CompanyTableSchema::bulkActions()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PartnerDetailsRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
