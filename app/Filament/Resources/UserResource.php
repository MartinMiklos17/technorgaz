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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Név'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // A cégkapcsolat kiválasztása (feltételezve, hogy a user tábla 'company_id' kulcsot használ)
                Forms\Components\Select::make('company_id')
                ->label('Cég')
                ->required()
                ->options(Company::all()->pluck('company_name', 'id')->toArray())
                ->searchable(),

                // Admin szerepkör
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin Jogosultság?'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Név'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.company_name')
                    ->searchable()
                    ->label('Kapcsolódó Cég Név'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Email Cím Hitelesítve'),
                Tables\Columns\ToggleColumn::make('is_admin')
                    ->label('Admin Jogosultsága van?')
                    ->sortable()
                    ->disabled(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Részletek'),
                Tables\Actions\EditAction::make()->label('Szerkesztés'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
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
