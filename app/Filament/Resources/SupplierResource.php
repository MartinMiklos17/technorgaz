<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?string $navigationGroup = 'Partnercégek';
    protected static ?string $pluralModelLabel   = 'Beszállítók';
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
                Forms\Components\Section::make('Kontakt adatok')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Név')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('taxnum')
                            ->label('Adószám')
                            ->maxLength(100)
                            ->default(null)
                            ->mask('99999999-9-99')
                            ->rule('regex:/^\d{8}-\d-\d{2}$/'),
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Kapcsolattartó neve')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefonszám')
                            ->tel()
                            ->maxLength(50)
                            ->default(null),
                    ]),
                Forms\Components\Section::make('Cím')
                    ->schema([
                        Forms\Components\TextInput::make('zip')
                            ->label('Irsz')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('city')
                            ->label('Város')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('street')
                            ->label('Utca')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('streetnumber')
                            ->label('Házszám')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('floor')
                            ->label('Emelet')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('door')
                            ->label('Ajtó')
                            ->maxLength(50),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip')
                    ->label('Irányítószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Város')
                    ->searchable(),
                Tables\Columns\TextColumn::make('street')
                    ->label('Utca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('streetnumber')
                    ->label('Házszám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('Emelet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('door')
                    ->label('Ajtó')
                    ->searchable(),
                Tables\Columns\TextColumn::make('taxnum')
                    ->label('Adószám')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Kapcsolattartó neve')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email cím')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefonszám')
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
