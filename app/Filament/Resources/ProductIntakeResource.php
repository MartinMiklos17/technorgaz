<?php

namespace App\Filament\Resources;

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

class ProductIntakeResource extends Resource
{
    protected static ?string $model = ProductIntake::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationGroup = 'Készletnyilvántartó';
    protected static ?string $pluralModelLabel = 'Bevételezés';

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
                Forms\Components\Section::make('Termékek')
                    ->schema([
                        Repeater::make('items')
                            ->label('Bevételezett termékek')
                            ->relationship() // automatikusan a hasMany kapcsolathoz igazodik
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
                    ])
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->label('Dátum'),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull()
                    ->label('Megjegyzés'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Beszállító')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label('Dátum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Tételek száma')
                    ->counts('items') // auto relationship count
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Összes érték (nettó)')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum(fn ($item) => $item->quantity * $item->unit_price);
                    })
                    ->money('HUF') // vagy 'EUR', ha úgy használod
                    ->sortable(),

                Tables\Columns\TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->note)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductIntakes::route('/'),
            'create' => Pages\CreateProductIntake::route('/create'),
            'view' => Pages\ViewProductIntake::route('/{record}'),
            'edit' => Pages\EditProductIntake::route('/{record}/edit'),
        ];
    }
}
