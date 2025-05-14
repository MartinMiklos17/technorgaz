<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Form as FilamentForm;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // Navigation & Labeling
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Termékek';
    protected static ?string $pluralModelLabel   = 'Termékek';
    protected static ?int $navigationSort=0;
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
                Forms\Components\Section::make('Alap adatok')
                    ->schema([
                        Forms\Components\TextInput::make('item_number')
                            ->label('Cikkszám')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record),
                        Forms\Components\TextInput::make('name')
                            ->label('Név')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Árak')
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Beszerzési Ár')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('consumer_price')
                            ->label('Fogyasztói Ár')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('service_price')
                            ->label('Szervizes')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('retail_price')
                            ->label('Kisker')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('wholesale_price')
                            ->label('Nagyker')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('handover_price')
                            ->label('Átadási')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                        Forms\Components\TextInput::make('service_partner_price')
                            ->label('Szervíz partner')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('HUF'),
                    ]),

                Forms\Components\Section::make('Leírás és Megjegyzés')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Leírás')
                            ->columnSpan('full'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Megjegyzés')
                            ->columnSpan('full'),
                    ]),

                Forms\Components\Section::make('Kategória és Csatolt Készülék')
                    ->schema([
                        Forms\Components\Select::make('product_category_id')
                            ->label('Termék Kategória')
                            ->relationship('productCategory', 'name')
                            ->searchable()
                            ->preload(),

                        // Self-referencing relation to an attached device
                        Forms\Components\Select::make('attached_device_id')
                            ->label('Csatolt Készülék')
                            ->relationship('attachedDevice', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('is_main_device')
                            ->label('Fő készülék?'),
                    ]),

                Forms\Components\Section::make('Láthatóság és Beállítások')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Státusz aktív?')
                            ->default(true),
                        Forms\Components\Toggle::make('show_in_main_carousel')
                            ->label('Főoldali carouselben megjelenik?'),
                        Forms\Components\Toggle::make('show_in_webshop')
                            ->label('Webshopban megjelenik?'),
                        Forms\Components\Toggle::make('has_electronic_installation_log')
                            ->label('Elektronikus beüzemelési napló?'),
                        Forms\Components\Toggle::make('show_in_spare_parts_list')
                            ->label('cserealkatrész listában megjelenik?'),
                    ]),

                    Forms\Components\Section::make('Méret és súly')
                    ->schema([
                        Forms\Components\TextInput::make('height')
                            ->label('Magasság (cm)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('cm'),

                        Forms\Components\TextInput::make('width')
                            ->label('Szélesség (cm)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('cm'),

                        Forms\Components\TextInput::make('depth')
                            ->label('Mélység (cm)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('cm'),

                        Forms\Components\TextInput::make('weight')
                            ->label('Súly (kg)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kg'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Készlet és fájlok')
                    ->schema([
                        Forms\Components\TextInput::make('low_stock_limit')
                            ->label('Alacsony készlet limit')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('inventory')
                            ->label('Készlet')
                            ->default(0)
                            ->numeric()
                            ->readOnly(),
                            // Multiple photo uploads (assuming you store them as JSON)
                        Forms\Components\FileUpload::make('photos')
                            ->disk('product_photos')
                            ->label('Fotók')
                            ->multiple()
                            ->directory(function (callable $get, ?Product $record) {
                                // Opcionálisan alkönyvtár user-hez kötve, pl. "uploads/user_123"
                                if ($record && $record->id) {
                                    return 'productPhoto_' . $record->id;
                                }
                            })
                            ->acceptedFileTypes(['image/*','application/pdf'])
                            ->imagePreviewHeight('200')
                            ->openable()         // Filament 3.x: engedélyez "megnyitás"
                            ->downloadable()     // engedélyez "letöltés"
                            ->previewable()      // képes előnézet
                            ->deletable()
                            ->hint('Kép vagy PDF'),
                        // Multiple datasheet uploads (also stored as JSON)
                        Forms\Components\FileUpload::make('datasheets')
                            ->disk('product_datasheets')
                            ->label('Adatlapok')
                            ->multiple()
                            ->directory(function (callable $get, ?Product $record) {
                                // Opcionálisan alkönyvtár user-hez kötve, pl. "uploads/user_123"
                                if ($record && $record->id) {
                                    return 'productDatasheet_' . $record->id;
                                }
                            })
                            ->acceptedFileTypes(['image/*','application/pdf'])
                            ->imagePreviewHeight('200')
                            ->openable()         // Filament 3.x: engedélyez "megnyitás"
                            ->downloadable()     // engedélyez "letöltés"
                            ->previewable()      // képes előnézet
                            ->deletable()
                            ->hint('Kép vagy PDF'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_number')
                    ->label('Cikkszám')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Név')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Státusz')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('show_in_webshop')
                    ->label('Webshop?')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('show_in_main_carousel')
                    ->label('Carouselben megjelenik?')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('has_electronic_installation_log')
                    ->label('Elektronikus beüzemelési napló?')
                    ->sortable(),
                Tables\Columns\TextColumn::make('inventory')
                    ->label('Készlet')
                    ->sortable(),
                Tables\Columns\TextColumn::make('productCategory.name')
                    ->label('Kategória')
                    ->sortable(),
            ])
            ->filters([
                // Add Filament table filters here if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Részletek'),
                Tables\Actions\EditAction::make()->label('Szerkesztés'),
            ])->defaultSort('inventory', 'asc');
    }

    public static function getRelations(): array
    {
        // If you create any relation managers (e.g., for product photos as a separate table),
        // you can register them here.
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
