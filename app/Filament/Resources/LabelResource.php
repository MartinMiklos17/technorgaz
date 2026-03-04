<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabelResource\Pages;
use App\Models\Label;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

use Filament\Forms\Get;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Címkék';
    protected static ?string $navigationLabel = 'Címkék';
    protected static ?string $pluralModelLabel = 'Címkék';
    protected static ?int $navigationSort = 1000;
    protected static ?string $modelLabel = 'Címke';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->active();
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form->schema([

            // =========================
            // ALAPADATOK
            // =========================
            Section::make('Alapadatok')
                ->schema([
                    Grid::make(1)->schema([

                        Select::make('type')
                            ->label('Típus')
                            ->options(Label::TYPES)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('type_text', Label::TYPES[(int) $state] ?? null);
                            }),

                        TextInput::make('title')
                            ->label('Termék neve (title)')
                            ->helperText('Legacy: automatikusan b1-ből képződik mentéskor.')
                            ->disabled()
                            ->dehydrated(false),

                        \Filament\Forms\Components\DateTimePicker::make('date')
                            ->label('Dátum')
                            ->seconds(false)
                            ->default(now())
                            ->required(),

                        Hidden::make('type_text'),
                        Hidden::make('payload')->default([]),
                    ]),
                ]),

            // =========================
            // TYPE 1 – HELYISÉGFŰTŐ
            // (a te "type 1 html" alapján)
            // =========================
            Section::make('Helyiségfűtő – adatok (Típus 1)')
                ->visible(fn (Get $get) => (int) $get('type') === 1)
                ->schema([

                    // ---- Címke adatok
                    Section::make('Címke adatok')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('payload.a1')->label('Beszállító neve')->maxLength(255),
                                TextInput::make('payload.a2')->label('Modellazonosító')->maxLength(255),
                                TextInput::make('payload.a3')->label('Szezonális helyiségfűtési energiahatékonyság')->maxLength(50),

                                TextInput::make('payload.a4')
                                    ->label('Hangteljesítmény')
                                    ->suffix('dB')
                                    ->maxLength(50),

                                TextInput::make('payload.a5')
                                    ->label('Mért hőteljesítmény')
                                    ->suffix('kW')
                                    ->maxLength(50),
                            ]),
                        ]),

                    // ---- Adattábla adatok
                    Section::make('Adattábla adatok')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('payload.b1')->label('Termék neve')->required()->maxLength(255),
                                TextInput::make('payload.b8')->label('Termék alcíme')->maxLength(255),
                                TextInput::make('payload.b2')->label('Készülékazonosító leírás')->maxLength(255),

                                TextInput::make('payload.b5')
                                    ->label('Éves energiafogyasztás')
                                    ->suffix('kW')
                                    ->maxLength(50),

                                Textarea::make('payload.b7')
                                    ->label('Óvintézkedések')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    // ---- Műszaki dokumentáció
                    Section::make('Műszaki dokumentáció')
                        ->schema([

                            Section::make('Hasznos hőteljesítmény')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('payload.c6')->label('Mért hőteljesítményen és magas hőmérsékleten')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c7')->label('A mért hőtelj. 30%-án és alacsony hőmérsékleten')->suffix('kW')->maxLength(50),
                                    ]),
                                ]),

                            Section::make('Hatásfok')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('payload.c8')->label('Mért hőteljesítményen és magas hőmérsékleten')->suffix('%')->maxLength(50),
                                        TextInput::make('payload.c9')->label('A mért hőtelj. 30%-án és alacsony hőmérsékleten')->suffix('%')->maxLength(50),
                                    ]),
                                ]),

                            Section::make('Villamosenergia fogyasztás')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('payload.c10')->label('Terhelés alatt')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c11')->label('Részterhelés alatt')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c12')->label('Készenléti üzemmódban')->suffix('kW')->maxLength(50),
                                    ]),
                                ]),

                            Section::make('Egyéb elemek')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('payload.c13')->label('Készenléti hőveszteség')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c14')->label('Nitrogén-oxid-kibocsátás (csak gáz v. olaj)')->suffix('mg/kWh')->maxLength(50),
                                    ]),

                                    CheckboxList::make('payload.c16')
                                        ->label('')
                                        ->columns(2)
                                        ->options([
                                            0 => 'Kondenzációs kazán.',
                                            1 => 'Alacsony hőmérsékletű kazán.',
                                            2 => 'B11 típusú kazán.',
                                            3 => 'Kapcsolt helyiségfűtő berendezés.',
                                            4 => 'Ha igen, rendelkezik-e kiegészitő fűtőberendezéssel.',
                                            5 => 'Kombinált fűtőkészülék.',
                                        ]),
                                ]),
                        ]),
                ]),

            // =========================
            // TYPE 2 – EGYEDI HELYISÉGFŰTŐ
            // (a te "type 2 html" alapján)
            // =========================
            Section::make('Egyedi helyiségfűtő – adatok (Típus 2)')
                ->visible(fn (Get $get) => (int) $get('type') === 2)
                ->schema([

                    // ---- Címke adatok
                    Section::make('Címke adatok')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('payload.a1')->label('Beszállító neve')->maxLength(255),
                                TextInput::make('payload.a2')->label('Modellazonosító')->maxLength(255),
                                TextInput::make('payload.a3')->label('Energiahatékonysági mutató')->maxLength(50),

                                // TYPE 2-ben a4/a5 = közvetlen/közvetett (kW)
                                TextInput::make('payload.a4')->label('Közvetlen hőteljesítmény')->suffix('kW')->maxLength(50),
                                TextInput::make('payload.a5')->label('Közvetett hőteljesítmény')->suffix('kW')->maxLength(50),
                            ]),
                        ]),

                    // ---- Adattábla adatok
                    Section::make('Adattábla adatok')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('payload.b1')->label('Termék neve')->required()->maxLength(255),

                                TextInput::make('payload.b3')
                                    ->label('Névleges hőteljesitményhez tartozó hatásfok')
                                    ->suffix('%')
                                    ->maxLength(50),

                                TextInput::make('payload.b5')
                                    ->label('Hangteljesítmény')
                                    ->suffix('dB')
                                    ->maxLength(50),

                                TextInput::make('payload.b6')
                                    ->label('NO kibocsátás')
                                    ->suffix('kWh')
                                    ->maxLength(50),

                                Textarea::make('payload.b4')
                                    ->label('Óvintézkedések')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    // ---- Műszaki dokumentáció adatok
                    Section::make('Műszaki dokumentáció adatok')
                        ->schema([

                            CheckboxList::make('payload.c8')
                                ->label('')
                                ->columns(1)
                                ->options([
                                    0 => 'Beltéri hőmérséklet-szabályozás jelenlét-érzékeléssel.',
                                    1 => 'Beltéri hőmérséklet-szabályozás nyitottablak-érzékeléssel.',
                                    2 => 'Távszabályozási lehetőség.',
                                    3 => 'Közvetett fűtési képesség.',
                                ]),

                            Grid::make(1)->schema([

                                Select::make('payload.c10')
                                    ->label('Tüzelőanyag típusának kiválasztása')
                                    ->options([
                                        'gáznemű' => 'gáznemű',
                                        'folyékony' => 'folyékony',
                                    ])
                                    ->native(false),

                                TextInput::make('payload.c9')
                                    ->label('Tüzelőanyag típusa')
                                    ->maxLength(50),

                                TextInput::make('payload.c11')
                                    ->label('Névleges hőteljesítmény')
                                    ->suffix('kW')
                                    ->maxLength(50),

                                TextInput::make('payload.c12')
                                    ->label('Minimális hőteljesítmény (indikatív)')
                                    ->suffix('kW')
                                    ->maxLength(50),

                                TextInput::make('payload.c13')
                                    ->label('Minimális hőteljesitményhez tartozó hatásfok (indikativ)')
                                    ->suffix('kW')
                                    ->maxLength(50),
                            ]),

                            Section::make('Kiegészítő villamosenergia fogyasztás...')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('payload.c14')->label('Névleges hőteljesíményen')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c15')->label('Minimális hőteljesítményen')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c16')->label('Készenléti üzemmódban')->suffix('kW')->maxLength(50),
                                        TextInput::make('payload.c17')->label('Gyújtóláng energiaigénye')->suffix('kW')->maxLength(50),

                                        Select::make('payload.c18')
                                            ->label('A teljesítmény illetve a beltéri hőmérséklet szabályozásának típusa')
                                            ->options([
                                                'Egyetlen állás, beltéri hőmérséklet-szabályozás nélkül' => 'Egyetlen állás, beltéri hőmérséklet-szabályozás nélkül',
                                                'Két vagy több kézi szabályozású állás, beltéri hőmérséklet-szabyályozás nélkül' => 'Két vagy több kézi szabályozású állás, beltéri hőmérséklet-szabyályozás nélkül',
                                                'Mechanikus termosztátos beltéri hőmérséklet-szabályozás' => 'Mechanikus termosztátos beltéri hőmérséklet-szabályozás',
                                                'Elektronikus beltéri hőmérséklet-szabályozás' => 'Elektronikus beltéri hőmérséklet-szabályozás',
                                                'Elektronikus beltéri hőmérséklet-szabályozás és napszak szerinti szabályozás' => 'Elektronikus beltéri hőmérséklet-szabályozás és napszak szerinti szabályozás',
                                                'Elektronikus beltéri hőmérséklet-szabályozás és heti szabályozás' => 'Elektronikus beltéri hőmérséklet-szabályozás és heti szabályozás',
                                            ])
                                            ->native(false)
                                            ->columnSpanFull(),
                                    ]),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Dátum')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Termék neve')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type_text')->label('Típus')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('cimke')
                    ->label('Címke')
                    ->url(fn (Label $record) => route('admin.label.get_img_1', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('adattabla')
                    ->label('Adattábla')
                    ->url(fn (Label $record) => route('admin.label.get_img_2', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('adatlap_pdf')
                    ->label('Adatlap (PDF)')
                    ->url(fn (Label $record) => route('admin.label.get_pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make()->label('Részletek'),
                Tables\Actions\EditAction::make()->label('Szerkesztés'),
                Tables\Actions\Action::make('torles')
                    ->label('Töröl')
                    ->requiresConfirmation()
                    ->action(function (Label $record) {
                        $record->status = 100;
                        $record->save();
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Típus')
                    ->options(Label::TYPES),

                Filter::make('title')
                    ->label('Termék neve')
                    ->form([
                        TextInput::make('value')->label('Termék neve'),
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when($data['value'] ?? null, fn ($q, $value) =>
                            $q->where('title', 'like', "%$value%")
                        )
                    ),

                Filter::make('date')
                    ->label('Dátum (tól-ig)')
                    ->form([
                        DatePicker::make('from')->label('Dátumtól'),
                        DatePicker::make('until')->label('Dátumig'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'] ?? null, fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabels::route('/'),
            'create' => Pages\CreateLabel::route('/create'),
            'edit' => Pages\EditLabel::route('/{record}/edit'),
            'view' => Pages\ViewLabel::route('/{record}'),
        ];
    }
}
