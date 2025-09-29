<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Enums\AccountType;
use App\Helpers\MapboxHelper;
use App\Forms\Components\MapboxField;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;

class CommissioningLogFormSchema
{
    public static function get(): array
    {
        return [

                Section::make('Készülék')
                ->schema([
                    Forms\Components\TextInput::make('serial_number')
                        ->label('Gyári szám')
                        ->required(),
                    Forms\Components\Toggle::make('has_sludge_separator')
                        ->label('Van iszapelválasztó')
                        ->required(),
                    Forms\Components\Select::make('product_id')
                        ->label('Készülék típusa')
                        ->options(fn () => Cache::remember('device_products_options', 3600, function () {
                            return Product::query()
                                ->where('is_main_device', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        }))
                        ->default(null)
                        ->native(false)
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('burner_pressure')
                        ->label('Égőnyomás')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('flue_gas_temperature')
                        ->label('Füstgáz hőmérséklet')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('co2_value')
                        ->label('co2 érték')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('co_value')
                        ->label('co érték')
                        ->numeric()
                        ->default(null),
                    Forms\Components\Toggle::make('has_eu_wind_grille')
                        ->label('Eu-s szabvány szélráccsal rendelkezik?')
                        ->required(),
                    Forms\Components\Toggle::make('safety_devices_ok')
                        ->label('Biztonsági elemek működnek')
                        ->required(),
                    Forms\Components\Toggle::make('flue_gas_backflow')
                        ->label('Füstgáz visszaáramlás')
                        ->required(),
                    Forms\Components\Toggle::make('gas_tight')
                        ->label('Készülék gáz tömör')
                        ->required(),
                    Forms\Components\TextInput::make('water_pressure')
                        ->label('Víznyomás')
                        ->numeric()
                        ->default(null),
                ])
                ->columns(1),
                Section::make('Vevő adatok')
                ->icon('heroicon-m-table-cells')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->required()
                        ->maxLength(191),
                    Forms\Components\TextInput::make('customer_zip')
                        ->maxLength(16)
                        ->default(null),
                    Forms\Components\TextInput::make('customer_city')
                        ->maxLength(191)
                        ->default(null),
                    Forms\Components\TextInput::make('customer_street')
                        ->maxLength(191)
                        ->default(null),
                    Forms\Components\TextInput::make('customer_street_number')
                        ->maxLength(32)
                        ->default(null),
                    Forms\Components\TextInput::make('customer_email')
                        ->email()
                        ->maxLength(191)
                        ->default(null),
                    Forms\Components\TextInput::make('customer_phone')
                        ->tel()
                        ->maxLength(64)
                        ->default(null),
                ]),
        ];
    }
}
