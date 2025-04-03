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
                Forms\Components\Select::make('user_id')
                    ->label('Felhasználó')
                    ->required()
                    ->options(User::all()->pluck('name', 'id')->toArray())
                    ->searchable(),
                Forms\Components\TextInput::make('company_name')
                    ->label('Cég neve')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_country')
                    ->label('Ország')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_zip')
                    ->label('Irányítószám')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_city')
                    ->label('Város')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_address')
                    ->label('Cím')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_taxnum')
                    ->label('Adószám')
                    ->mask('99999999-9-99')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Cég neve')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_country')
                    ->label('Ország')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_zip')
                    ->label('Irányítószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_city')
                    ->label('Város')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_address')
                    ->label('Cím')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_taxnum')
                    ->label('Adószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Módosítva')
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
                    Tables\Actions\DeleteBulkAction::make()->label('Törlés')                  ->modalSubmitActionLabel('Mentés')
                    ->modalHeading('Partner Adatok Törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott Céget?')
                    ->modalcancelActionLabel('Mégse'),
                ])->label('Törlés')
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
