<?php
namespace App\Forms\Schemas;

use App\Filament\Resources\ProductIntakeResource\Pages;
use App\Models\Product;
use App\Models\ProductIntake;
use App\Models\ProductIntakeItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Forms\Components\ZipLookupField;
class ProductIntakeFormSchema
{
    public static function get(): array
    {
        return [
                Forms\Components\Section::make('Termékek')
                    ->schema([
                        Repeater::make('items')
                            ->addAction(
                                fn (\Filament\Forms\Components\Actions\Action $action) =>
                                    $action
                                        ->label('➕ Termék hozzáadása')
                                        ->button()
                                        ->color('success') // 'primary', 'success', 'danger', 'gray', stb.
                                        ->size('lg')       // 'sm', 'md', 'lg'
                            )
                            ->label('Bevételezett termékek')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Termék')
                                    ->options(Product::all()->pluck('name', 'id')->toArray())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->purchase_price ?? 0);
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label('Mennyiség')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Beszerzési ár')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(3),
                    ]),
                Forms\Components\Select::make('supplier_id')
                    ->label('Beszállító')
                    ->preload()
                    ->relationship(
                        name: 'supplier',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->orderBy('name')
                    )
                    ->createOptionForm([
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
                            ZipLookupField::make('zip')
                                ->required()
                                ->cityField('city')
                                ->label(__('Irsz'))
                                ->maxLength(255),
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
                    ])
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->label('Dátum')
                    ->native(false),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull()
                    ->label('Megjegyzés'),
        ];
    }
}
