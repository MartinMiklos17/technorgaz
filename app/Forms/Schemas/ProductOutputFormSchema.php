<?php
namespace App\Forms\Schemas;

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
use App\Forms\Schemas\CustomerFormSchema;
use App\Models\Company;
use App\Models\User;
use App\Tables\Schemas\ProductOutputTableSchema;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Support\Colors\Color;
class ProductOutputFormSchema
{
    public static function get(): array
    {
        return [ Section::make('Vevő Adatok és dátum')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                    ->label('Vevő')
                    ->relationship('customer', 'billing_name')
                    ->searchable()
                    ->reactive() // <<< FONTOS
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        ...CustomerFormSchema::get(),
                    ])
                    ->afterStateUpdated(function ($state) {
                        $customer = \App\Models\Customer::find($state);

                        if ($customer && is_null($customer->account_type)) {
                            Notification::make()
                                ->title('❗ Hiányzó fióktípus a vevőnél!')
                                ->body('A kiválasztott vevőhöz nincs beállítva fióktípus (account_type), így az automatikus ár kiválasztás nem fog működni.')
                                ->icon('heroicon-o-exclamation-circle') // vagy: heroicon-o-ban
                                ->color(Color::Red) // piros stílus
                                ->persistent()
                                ->actions([
                                    Action::make('go-to-customer')
                                        ->label('Vevő szerkesztése')
                                        ->url(route('filament.admin.resources.customers.edit', ['record' => $customer->id]))
                                        ->openUrlInNewTab()
                                        ->color('gray'),
                                ])
                                ->send();
                        }
                    }),
                Forms\Components\DatePicker::make('date')
                    ->label('Kiadás dátuma')
                    ->native(false)
                    ->required(),
                ]),
                Section::make('Fizetési mód')
                    ->schema([
                        Forms\Components\Select::make('payment_method') // <<< ÚJ MEZŐ
                        ->label('Fizetési mód')
                        ->options([
                            'cash' => 'Készpénz',
                            'card' => 'Bankkártya',
                            'transfer' => 'Átutalás',
                            'other' => 'Egyéb',
                        ])
                        ->required(),
                    ])->columns(1),
                Section::make('Termékek')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                        ->addAction(
                            fn (\Filament\Forms\Components\Actions\Action $action) =>
                                $action
                                    ->label('➕ Termék hozzáadása')
                                    ->button()
                                    ->color('success') // 'primary', 'success', 'danger', 'gray', stb.
                                    ->size('lg')       // 'sm', 'md', 'lg'
                        )
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
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $customerId = $get('../../customer_id'); // így működik repeaterben
                                    $product = \App\Models\Product::find($state);
                                    $customer = \App\Models\Customer::find($customerId);

                                    if ($product && $customer && $customer->account_type) {
                                        $accountToPriceField = [
                                            'service_partner' => 'service_partner_price',
                                            'handover' => 'handover_price',
                                            'wholesale' => 'wholesale_price',
                                            'retail' => 'retail_price',
                                            'service' => 'service_price',
                                            'consumer' => 'consumer_price',
                                        ];

                                        $priceField = $accountToPriceField[$customer->account_type] ?? null;

                                        if ($priceField && isset($product->{$priceField})) {
                                            $set('price_type', $priceField);
                                            $set('selected_price', $product->{$priceField});
                                        }
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
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $product = \App\Models\Product::find($get('product_id'));
                                    if ($product && $state > $product->inventory) {
                                        $set('quantity', $product->inventory); // visszaállítjuk
                                    }
                                })
                                ->maxValue(function (callable $get) {
                                    $product = \App\Models\Product::find($get('product_id'));
                                    return $product?->inventory ?? null;
                                })
                                ->helperText(fn (callable $get) =>
                                    $get('product_id') ?
                                    'Elérhető készlet: ' . (\App\Models\Product::find($get('product_id'))?->inventory ?? 0) . ' db'
                                    : 'Válassz először terméket.')
                                ->rule(function (callable $get) {
                                        $product = \App\Models\Product::find($get('product_id'));
                                        return 'max:' . ($product?->inventory ?? 999999);
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
                                ->default(0),
                            Section::make('ÁFA, Garancia, Cserealkatrész')
                                ->schema([
                                    Forms\Components\Toggle::make('is_vat_included')
                                        ->label('Áfát tartalmaz?')
                                        ->default(false),
                                    Forms\Components\Toggle::make('warranty')
                                        ->label('Garanciális termék')
                                        ->default(false)
                                        ->reactive(),
                                    Forms\Components\TextInput::make('serial_number')
                                        ->label('Gyári szám')
                                        ->reactive()
                                        ->visible(fn (callable $get) => $get('warranty') === true)
                                        ->requiredIf('warranty', true)
                                        ->maxLength(255),
                                    Forms\Components\Toggle::make('spare_part_returned')
                                        ->label('Cserealkatrész leadva')
                                        ->default(false),
                                ])->columns(3),
                        ])
                    ->columns(3)
                    ->required(),
                ]),
                Forms\Components\Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull()
                    ->nullable(),
        ];
    }
}
