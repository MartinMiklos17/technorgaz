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
use App\Models\Company;
use App\Models\User;

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
                Section::make('Vavő Adatok és dátum')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                    ->label('Vevő')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                    ])
                    ->required(),
                    Forms\Components\DatePicker::make('date')
                    ->label('Kiadás dátuma')
                    ->required(),
                ]),

                Section::make('Termékek')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                        ->label('Kiadott termékek')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Termék')
                                ->options(
                                    \App\Models\Product::all()->pluck('name', 'id')->toArray()
                                )
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $product = \App\Models\Product::find($state);
                                    if ($product) {
                                        // előtöltéshez beállítunk egy default ár értéket
                                        $set('selected_price', $product->price_a ?? 0);
                                    }
                                }),
                                Forms\Components\Select::make('price_type')
                                ->label('Ártípus')
                                ->options([
                                    'purchase_price' => 'Beszerzési ár',
                                    'consumer_price' => 'Fogyasztói ár',
                                    'service_price' => 'Szerviz ár',
                                    'retail_price' => 'Kisker ár',
                                    'wholesale_price' => 'Nagyker ár',
                                    'handover_price' => 'Átadáskori ár',
                                    'service_partner_price' => 'Szervizpartner ár',
                                ])
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $product = \App\Models\Product::find($get('product_id'));
                                    if ($product && $state) {
                                        $set('selected_price', $product->{$state} ?? 0);
                                    }
                                })
                                ->required(),


                            Forms\Components\TextInput::make('selected_price')
                                ->label('Kiválasztott ár')
                                ->numeric()
                                ->required()
                                ->readOnly(),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Mennyiség')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $product = \App\Models\Product::find($get('product_id'));
                                    if ($product && $state > $product->inventory) {
                                        $set('quantity', $product->inventory); // ne lépje túl a készletet
                                    }
                                }),

                            Forms\Components\Select::make('discount')
                                ->label('Kedvezmény (%)')
                                ->options([
                                    0 => '0%',
                                    5 => '5%',
                                    10 => '10%',
                                    15 => '15%',
                                    20 => '20%',
                                    100 => '100%',
                                ])
                                ->default(0)
                                ->required(),
                            Section::make('ÁFA, Garancia, Cserealkatrész')
                                ->schema([
                                    Forms\Components\Toggle::make('is_vat_included')
                                        ->label('Áfát tartalmaz?')
                                        ->default(false),

                                    Forms\Components\Toggle::make('warranty')
                                        ->label('Garanciális termék')
                                        ->default(false),

                                    Forms\Components\Toggle::make('spare_part_returned')
                                        ->label('Cserealkatrész leadva')
                                        ->default(false),
                                ])->columns(3)
                        ])
                    ->columns(3)
                    ->required(),
                ]),
                Forms\Components\Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('partner_detail_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
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
