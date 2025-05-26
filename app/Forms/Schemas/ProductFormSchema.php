<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use App\Models\Product;
class ProductFormSchema
{
    public static function get(): array
    {
        return [
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
                            ->preload()
                            ->createOptionForm(\App\Forms\Schemas\ProductCategoryFormSchema::get())
                            ->createOptionAction(function (\Filament\Forms\Components\Actions\Action $action) {
                                return $action->modalHeading('Új kategória létrehozása');
                            }),

                        // Self-referencing relation to an attached device
                        Forms\Components\Select::make('attached_device_id')
                            ->label('Csatolt Készülék')
                            ->relationship(
                                name: 'attachedDevice',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_main_device', true)
                            )
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('is_main_device')
                            ->label('Készülék?'),
                    ]),

                Forms\Components\Section::make('Láthatóság és Beállítások')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Státusz aktív?')
                            ->default(true),
                        Forms\Components\Toggle::make('show_in_webshop')
                            ->label('Webshopban megjelenik?'),
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
        ];
    }
}
