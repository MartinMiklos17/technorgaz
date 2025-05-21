<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\RelationManagers\PartnerDetailsRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers\CompanyRelationManager;
use App\Models\PartnerDetails;
use Filament\Tables\Columns\CheckboxColumn;
use App\Models\Company;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use App\Tables\Schemas\UserTableSchema;
use App\Forms\Schemas\UserFormSchema;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup='Felhasználók';
    protected static ?string $pluralModelLabel   = 'Felhasználó lista';
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Felhasználó létrehozva!';
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...UserFormSchema::get()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...UserTableSchema::columns()
            ])
            ->filters([
                ...UserTableSchema::filters()
            ])
            ->actions([
                ...UserTableSchema::actions()
            ])
            ->bulkActions([
                ...UserTableSchema::bulkActions()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CompanyRelationManager::class,
            PartnerDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
