<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Partnercégek';
    protected static ?string $pluralModelLabel = 'Vevők';

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
                Section::make('Adatok')
                ->schema([
                    Forms\Components\TextInput::make('name')->label("Név")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('zip')->label("Irányítószám")
                        ->required()
                        ->maxLength(20)
                        ->default(null),
                    Forms\Components\TextInput::make('city')->label("Város")
                        ->required()
                        ->maxLength(255)
                        ->default(null),
                    Forms\Components\TextInput::make('street')->label("Utca")
                        ->required()
                        ->maxLength(255)
                        ->default(null),
                    Forms\Components\TextInput::make('streetnumber')->label("Házszám")
                        ->required()
                        ->maxLength(50)
                        ->default(null),
                    Forms\Components\TextInput::make('floor')->label("Emelet")
                        ->maxLength(50)
                        ->default(null),
                    Forms\Components\TextInput::make('door')->label("Ajtó")
                        ->maxLength(50)
                        ->default(null),
                ]),
                Section::make('Számlázási Adatok')
                ->schema([
                Forms\Components\Toggle::make('billing_same_as_main')
                    ->label('Számlázási adatok megegyeznek az alap címmel')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $set('billing_name', $get('name'));
                            $set('billing_zip', $get('zip'));
                            $set('billing_city', $get('city'));
                            $set('billing_street', $get('street'));
                            $set('billing_streetnumber', $get('streetnumber'));
                            $set('billing_floor', $get('floor'));
                            $set('billing_door', $get('door'));
                        }
                    }),
                Forms\Components\TextInput::make('billing_name')->label("Számlázási Név")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_zip')->label("Számlázási Irányítószám")
                    ->required()
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('billing_city')->label("Számlázási Város")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_street')->label("Számlázási Utca")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('billing_streetnumber')->label("Számlázási Házszám")
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('billing_floor')->label("Számlázási Emelet")
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('billing_door')->label("Számlázási Ajtó")
                    ->maxLength(50)
                    ->default(null),
                ]),
                Section::make('Szállítási Adatok')
                ->schema([
                Forms\Components\Toggle::make('postal_same_as_main')
                    ->label('Szállítási adatok megegyeznek az alap címmel')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $set('postal_name', $get('name'));
                            $set('postal_zip', $get('zip'));
                            $set('postal_city', $get('city'));
                            $set('postal_street', $get('street'));
                            $set('postal_streetnumber', $get('streetnumber'));
                            $set('postal_floor', $get('floor'));
                            $set('postal_door', $get('door'));
                        }
                    }),
                Forms\Components\TextInput::make('postal_name')->label("Szállítási Név")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_zip')->label("Szállítási Irányítószám")
                    ->required()
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('postal_city')->label("Szállítási Város")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_street')->label("Szállítási Utca")
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postal_streetnumber')->label("Szállítási Házszám")
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('postal_floor')->label("Szállítási Emelet")
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('postal_door')->label("Szállítási Ajtó")
                    ->maxLength(50)
                    ->default(null),
                ]),
                Section::make('További Adatok')
                ->schema([
                Forms\Components\TextInput::make('taxnumber')->label("Adószám")
                    ->mask('99999999-9-99')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('contact_name')->label("Kontakt Név")
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('contact_email')->label("Email")
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('contact_phone')->label("Telefonszám")
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label("Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip')->label("Irányítószám")
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')->label("Város")
                    ->searchable(),
                Tables\Columns\TextColumn::make('street')->label("Utca")
                    ->searchable(),
                Tables\Columns\TextColumn::make('streetnumber')->label("Házszám")
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')->label("Emelet")
                    ->searchable(),
                Tables\Columns\TextColumn::make('door')->label("Ajtó")
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_name')->label("Számlázási Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_address')->label("Számlázási Cím")
                    ->label('Számlázási cím')
                    ->getStateUsing(fn ($record) => "{$record->billing_zip} {$record->billing_city}, {$record->billing_street} {$record->billing_streetnumber}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_name')->label("Szállítási Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_address')->label("Szállítási Cím")
                    ->label('Szállítási cím')
                    ->getStateUsing(fn ($record) => "{$record->postal_zip} {$record->postal_city}, {$record->postal_street} {$record->postal_streetnumber}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('taxnumber')->label("Adószám")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')->label("Kontakt Név")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_email')->label("Email")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')->label("Telefonszám")
                    ->searchable(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
