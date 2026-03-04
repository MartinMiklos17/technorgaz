<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ServiceReport;
use Illuminate\Support\Carbon;
use App\Models\CommissioningLog;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

// Plugin
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use DefStudio\SearchableInput\DTO\SearchResult;

class ServiceReportFormSchema
{
    public static function get(): array
    {
        return [
            Section::make('Készülék azonosítás')
                ->description(function (Get $get) {
                    $id = $get('commissioning_log_id');
                    if (! $id) {
                        return 'Gépeld be a gyári számot és válassz a listából. '
                             . 'Csak választás után kapcsoljuk össze a beüzemelési naplóval. '
                             . 'Garanciális ablakok és státuszok csak összekapcsolt készüléknél jelennek meg.';
                    }

                    $log = CommissioningLog::find($id);
                    if (! $log) {
                        return 'A kiválasztott napló nem található.';
                    }

                    $lines = [];
                    $lines[] = 'Gyári szám: <strong>'.e($log->serial_number).'</strong>';
                    $lines[] = 'Ügyfél: <strong>'.e($log->customer_name).'</strong>';

                    if ($log->created_at) {
                        $base  = Carbon::parse($log->created_at);
                        $now   = Carbon::now();

                        $win1a = $base->copy()->addMonthsNoOverflow(10);
                        $win1b = $base->copy()->addMonthsNoOverflow(13);
                        $win2a = $base->copy()->addMonthsNoOverflow(22);
                        $win2b = $base->copy()->addMonthsNoOverflow(25);
                        $until = $base->copy()->addYearsNoOverflow(3);

                        $lines[] = 'Beüzemelés: <strong>'.$base->format('Y.m.d').'</strong>';
                        $lines[] = '1. karbantartási ablak: <strong>'.$win1a->format('Y.m.d').' – '.$win1b->format('Y.m.d').'</strong>';
                        $lines[] = '2. karbantartási ablak: <strong>'.$win2a->format('Y.m.d').' – '.$win2b->format('Y.m.d').'</strong>';
                        $lines[] = 'Garancia vége (max): <strong>'.$until->format('Y.m.d').'</strong>';

                        $done = ServiceReport::query()
                            ->where('commissioning_log_id', $log->id)
                            ->where('report_type', 'maintenance_warranty')
                            ->pluck('created_at')
                            ->map(fn ($d) => Carbon::parse($d));

                        $firstDone  = $done->contains(fn (Carbon $d) => $d->betweenIncluded($win1a, $win1b));
                        $secondDone = $done->contains(fn (Carbon $d) => $d->betweenIncluded($win2a, $win2b));

                        $nowInWin1 = $now->betweenIncluded($win1a, $win1b);
                        $nowInWin2 = $now->betweenIncluded($win2a, $win2b);

                        $firstFuture  = $now->lt($win1a);
                        $secondFuture = $now->lt($win2a);

                        $lines[] = '1. karbantartás teljesítve: ' . (
                            $firstDone
                                ? '<span style="color:#16a34a;font-weight:600">Igen</span>'
                                : ($firstFuture
                                    ? '<span style="color:#6b7280;font-weight:600">Nem aktuális</span>'
                                    : '<span style="color:#dc2626;font-weight:600">Nem</span>')
                        );

                        $lines[] = '2. karbantartás teljesítve: ' . (
                            $secondDone
                                ? '<span style="color:#16a34a;font-weight:600">Igen</span>'
                                : ($secondFuture
                                    ? '<span style="color:#6b7280;font-weight:600">Nem aktuális</span>'
                                    : '<span style="color:#dc2626;font-weight:600">Nem</span>')
                        );

                        if ($nowInWin1) {
                            $lines[] = '<span style="color:#16a34a;font-weight:600">Most az 1. karbantartási ablakban vagy – garanciális karbantartás elvégezhető.</span>';
                        } elseif ($nowInWin2 && $firstDone) {
                            $lines[] = '<span style="color:#16a34a;font-weight:600">Most a 2. ablakban vagy – garanciális karbantartás elvégezhető (az 1. megvolt).</span>';
                        } elseif ($nowInWin2 && ! $firstDone) {
                            $lines[] = '<span style="color:#dc2626;font-weight:600">A 2. ablakban vagy, de az 1. garanciális karbantartás nem teljesült – garanciális karbantartás nem vehető fel.</span>';
                        }

                        $canRepairWarranty =
                            $now->lte($until) && (
                                $now->lt($win1b)
                                || ($firstDone && $now->lt($win2b))
                                || ($firstDone && $secondDone)
                            );

                        $lines[] = $canRepairWarranty
                            ? '<span style="color:#16a34a;font-weight:700">Javítás garanciában: LEHETSÉGES</span>'
                            : '<span style="color:#dc2626;font-weight:700">Javítás garanciában: NEM lehetséges</span>';
                    }

                    return new HtmlString(implode('<br>', $lines));
                })
                ->aside()
                ->schema([
                    // 1) Sorozatszám: kereshető autocomplete, de SEMMI automatikus linkelés
                SearchableInput::make('serial_number')
                    ->label('Készülék gyári száma')
                    ->placeholder('Kezdj gépelni és válassz a listából')
                    ->reactive()
                    ->live(debounce: 400)
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('scan')
                            ->icon('heroicon-m-qr-code')
                            ->modalContent(view('filament.components.qr-scanner'))
                            ->modalHeading('Kód beolvasása')
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                    )
                    ->searchUsing(function (string $search) {
                        return CommissioningLog::query()
                            ->where('serial_number', 'like', "%{$search}%")
                            ->orderByDesc('created_at')
                            ->limit(15)
                            ->get()
                            ->map(function (CommissioningLog $log) {
                                $label = sprintf(
                                    '[%s] %s — %s',
                                    optional($log->created_at)->format('Y.m.d'),
                                    $log->serial_number,
                                    $log->customer_name
                                );

                                return SearchResult::make($log->serial_number, $label)
                                    ->withData('commissioning_log_id', $log->id)
                                    ->withData('product_id',          $log->product_id)
                                    ->withData('customer_name',       $log->customer_name)
                                    ->withData('customer_zip',        $log->customer_zip)
                                    ->withData('customer_city',       $log->customer_city)
                                    ->withData('customer_street',     $log->customer_street)
                                    ->withData('customer_street_number', $log->customer_street_number)
                                    ->withData('customer_email',      $log->customer_email)
                                    ->withData('customer_phone',      $log->customer_phone);
                            })
                            ->toArray();
                    })
                    ->onItemSelected(function (SearchResult $item, Set $set) {
                        // 👉 A FELHASZNÁLÓ VÁLASZTOTT → linkelünk és kitöltünk
                        $set('serial_number', $item->value());
                        $set('commissioning_log_id', $item->get('commissioning_log_id'));
                        $set('product_id',           $item->get('product_id'));

                        if ($pid = $item->get('product_id')) {
                            $p = Product::find($pid);
                            $set('product_name',        $p?->name);
                            $set('product_description', $p?->description);
                        } else {
                            $set('product_name',        null);
                            $set('product_description', null);
                        }

                        // ügyfél adatok
                        $set('customer_name',        $item->get('customer_name'));
                        $set('customer_zip',         $item->get('customer_zip'));
                        $set('customer_city',        $item->get('customer_city'));
                        $set('customer_street',      $item->get('customer_street'));
                        $set('customer_street_number', $item->get('customer_street_number'));
                        $set('customer_email',       $item->get('customer_email'));
                        $set('customer_phone',       $item->get('customer_phone'));
                    })
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        // 👉 Ha átírják vagy kiürítik → töröljünk minden kapcsolt adatot
                        $currentLinkId = $get('commissioning_log_id');
                        if (! $currentLinkId) {
                            return;
                        }

                        $new = mb_strtolower(trim((string) $state));
                        $linkedSn = optional(CommissioningLog::find($currentLinkId))->serial_number;

                        if ($new === '' || !$linkedSn || $new !== mb_strtolower($linkedSn)) {
                            // Leválasztás és minden automatikus adat törlése
                            $set('commissioning_log_id', null);
                            $set('product_id',           null);
                            $set('product_name',         null);
                            $set('product_description',  null);

                            $set('customer_name',        null);
                            $set('customer_zip',         null);
                            $set('customer_city',        null);
                            $set('customer_street',      null);
                            $set('customer_street_number', null);
                            $set('customer_email',       null);
                            $set('customer_phone',       null);
                        }
                    })
                    ->helperText('A kapcsolat csak akkor jön létre, ha a listából választasz. Ha átírod a sorozatszámot, a kapcsolt adatok törlődnek.'),

                    // rejtett kapcsolat (csak kiválasztás után lesz kitöltve)
                    Hidden::make('commissioning_log_id'),

                    // 2) Jegyzőkönyv típus – garanciális opciók csak kapcsolt naplónál
                    Select::make('report_type')
                    ->label('Jegyzőkönyv típusa')
                    ->required()
                    ->reactive()
                    ->options(function (Get $get) {
                        // 6 lehetséges érték központi labeljei
                        $labels = [
                            'maintenance_warranty'                 => 'Karbantartás (garanciális)',
                            'maintenance_non_warranty'             => 'Karbantartás (garancián kívüli)',
                            'repair_warranty'                      => 'Javítás (garanciális)',
                            'repair_non_warranty'                  => 'Javítás (garancián kívüli)',
                            'maintenance_not_covered_by_warranty'  => 'Garanciába nem vehető készülék karbantartás',
                            'repair_not_covered_by_warranty'       => 'Garanciába nem vehető készülék javítás',
                        ];

                        $logId = $get('commissioning_log_id');

                        // ❌ NINCS beüzemelési napló → csak a 2 új opció
                        if (! $logId) {
                            return [
                                'maintenance_not_covered_by_warranty' => $labels['maintenance_not_covered_by_warranty'],
                                'repair_not_covered_by_warranty'      => $labels['repair_not_covered_by_warranty'],
                            ];
                        }

                        // ✅ VAN beüzemelési napló → marad a dátum-alapú 4-es logika
                        $log = \App\Models\CommissioningLog::find($logId);
                        if (! $log || ! $log->created_at) {
                            // ha valamiért nincs dátum, vésztartalék: csak a nem garanciális klasszikus opciók
                            return [
                                'maintenance_non_warranty' => $labels['maintenance_non_warranty'],
                                'repair_non_warranty'      => $labels['repair_non_warranty'],
                            ];
                        }

                        $now   = \Illuminate\Support\Carbon::now();
                        $base  = \Illuminate\Support\Carbon::parse($log->created_at);
                        $win1a = (clone $base)->addMonthsNoOverflow(10);
                        $win1b = (clone $base)->addMonthsNoOverflow(13);
                        $win2a = (clone $base)->addMonthsNoOverflow(22);
                        $win2b = (clone $base)->addMonthsNoOverflow(25);
                        $until = (clone $base)->addYearsNoOverflow(3);

                        $done = \App\Models\ServiceReport::query()
                            ->where('commissioning_log_id', $logId)
                            ->where('report_type', 'maintenance_warranty')
                            ->pluck('created_at')
                            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d));

                        $firstDone  = $done->contains(fn ($d) => $d->betweenIncluded($win1a, $win1b));
                        $secondDone = $done->contains(fn ($d) => $d->betweenIncluded($win2a, $win2b));

                        // Ha mindkét karbantartás megvan → csak javítás (garanciális, ha még határidőn belül)
                        if ($firstDone && $secondDone) {
                            return $now->lte($until)
                                ? ['repair_warranty' => $labels['repair_warranty']]
                                : ['repair_non_warranty' => $labels['repair_non_warranty']];
                        }

                        $canMaintenanceWarranty =
                            $now->betweenIncluded($win1a, $win1b) ||
                            ($now->betweenIncluded($win2a, $win2b) && $firstDone);

                        $canRepairWarranty =
                            $now->lte($until) && (
                                $now->lt($win1b) ||
                                ($firstDone && $now->lt($win2b)) ||
                                ($firstDone && $secondDone)
                            );

                        if ($canMaintenanceWarranty || $canRepairWarranty) {
                            $out = [];
                            if ($canMaintenanceWarranty) $out['maintenance_warranty'] = $labels['maintenance_warranty'];
                            if ($canRepairWarranty)      $out['repair_warranty']      = $labels['repair_warranty'];
                            // a két klasszikus nem-garanciálisat is engedjük (ha szeretnéd, kivehetők)
                            $out['maintenance_non_warranty'] = $labels['maintenance_non_warranty'];
                            $out['repair_non_warranty']      = $labels['repair_non_warranty'];
                            return $out;
                        }

                        // alap: a két klasszikus nem-garanciális
                        return [
                            'maintenance_non_warranty' => $labels['maintenance_non_warranty'],
                            'repair_non_warranty'      => $labels['repair_non_warranty'],
                        ];
                    })
                    ->helperText(fn (Get $get) => $get('commissioning_log_id')
                        ? 'Kapcsolt beüzemelési napló esetén a garanciális opciók a dátumok alapján jelennek meg.'
                        : 'Nincs beüzemelési napló: csak „Garanciába nem vehető …” opciók választhatók.'
                    )
                    ->native(false)
                    // Extra védelem: garanciális típus NEM választható napló nélkül
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $warrantyTypes = ['maintenance_warranty', 'repair_warranty'];
                            if (in_array($value, $warrantyTypes, true) && ! $get('commissioning_log_id')) {
                                $fail('Garanciális jegyzőkönyvhöz válassz beüzemelési naplót.');
                            }
                        };
                    }),

                    Hidden::make('product_id'),

                    TextInput::make('product_name')
                        ->label('Készülék típusa')
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($state, Set $set, Get $get) {
                            if (!$state && $id = $get('product_id')) {
                                $p = Product::find($id);
                                $set('product_name', $p?->name);
                            }
                        }),

                    Textarea::make('product_description')
                        ->label('Készülék leírás')
                        ->rows(3)
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($state, Set $set, Get $get) {
                            if (!$state && $id = $get('product_id')) {
                                $p = Product::find($id);
                                $set('product_description', $p?->description);
                            }
                        }),
                ]),

            Section::make('Készülék technikai adatok')
                ->schema([
                    Forms\Components\TextInput::make('burner_pressure')->label('Égőnyomás')->numeric()->default(null)->required(),
                    Forms\Components\TextInput::make('flue_gas_temperature')->label('Füstgáz hőmérséklet')->numeric()->default(null)->required(),
                    Forms\Components\TextInput::make('co2_value')->label('co2 érték')->numeric()->default(null)->required(),
                    Forms\Components\TextInput::make('co_value')->label('co érték')->numeric()->default(null)->required(),
                    Forms\Components\TextInput::make('water_pressure')->label('Víznyomás')->numeric()->default(null)->required(),
                    Forms\Components\Radio::make('has_sludge_separator')->label('Van iszapelválasztó')->options([1 => 'Igen', 0 => 'Nem'])->inline()->required(),
                    Forms\Components\Radio::make('has_eu_wind_grille')->label('Eu-s szabvány szélráccsal rendelkezik?')->options([1 => 'Igen', 0 => 'Nem'])->inline()->required(),
                    Forms\Components\Radio::make('safety_devices_ok')->label('Biztonsági elemek működnek')->options([1 => 'Igen', 0 => 'Nem'])->inline()->required(),
                    Forms\Components\Radio::make('flue_gas_backflow')->label('Füstgáz visszaáramlás')->options([1 => 'Igen', 0 => 'Nem'])->inline()->required(),
                    Forms\Components\Radio::make('gas_tight')->label('Készülék gáz tömör')->options([1 => 'Igen', 0 => 'Nem'])->inline()->required(),
                    Forms\Components\Radio::make('correct_phase_connection')->label('Fázis helyes bekötése')->options([1 => 'Igen', 0 => 'Nem'])->inline()->default(0)->required(),
                ]),

            Section::make('Ügyfél adatai - CSAK VÁLTOZÁS ESETÉN TÖLTENI!')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('customer_name')->label('Név')->required()->reactive(),
                        TextInput::make('customer_email')->label('E-mail')->email()->reactive(),
                        TextInput::make('customer_phone')->label('Telefon')->reactive(),
                        TextInput::make('customer_zip')->label('Irányítószám')->reactive(),
                        TextInput::make('customer_city')->label('Város')->reactive(),
                        TextInput::make('customer_street')->label('Utca')->reactive(),
                        TextInput::make('customer_street_number')->label('Házszám')->reactive(),
                    ]),
                ]),

            Section::make('Tulajdonos / Fenntartó')
                ->schema([
                    Toggle::make('owner_is_different')
                        ->label('Tulajdonos egyezik az ügyféllel?')
                        ->reactive()
                        ->afterStateHydrated(function ($state, Set $set, Get $get) {
                            if ($state) {
                                $set('owner_name',           $get('customer_name'));
                                $set('owner_email',          $get('customer_email'));
                                $set('owner_phone',          $get('customer_phone'));
                                $set('owner_zip',            $get('customer_zip'));
                                $set('owner_city',           $get('customer_city'));
                                $set('owner_street',         $get('customer_street'));
                                $set('owner_street_number',  $get('customer_street_number'));
                            }
                        })
                        ->afterStateUpdated(function (bool $state, Set $set, Get $get) {
                            if (!$state) {
                                $set('owner_name', null);
                                $set('owner_email', null);
                                $set('owner_phone', null);
                                $set('owner_zip', null);
                                $set('owner_city', null);
                                $set('owner_street', null);
                                $set('owner_street_number', null);
                            } else {
                                $set('owner_name',           $get('customer_name'));
                                $set('owner_email',          $get('customer_email'));
                                $set('owner_phone',          $get('customer_phone'));
                                $set('owner_zip',            $get('customer_zip'));
                                $set('owner_city',           $get('customer_city'));
                                $set('owner_street',         $get('customer_street'));
                                $set('owner_street_number',  $get('customer_street_number'));
                            }
                        }),

                    Grid::make(2)->schema([
                        TextInput::make('owner_name')->label('Tulajdonos neve')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_email')->label('Tulajdonos e-mail')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_phone')->label('Tulajdonos telefon')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_zip')->label('Irányítószám')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_city')->label('Város')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_street')->label('Utca')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                        TextInput::make('owner_street_number')->label('Házszám')
                            ->disabled(fn (Get $get) => ! $get('owner_is_different')),
                    ])->columns(7),

                    Toggle::make('maintainer_same_as_customer')
                        ->label('Fenntartó egyezik az ügyféllel?')
                        ->reactive()
                        ->afterStateHydrated(function ($state, Set $set, Get $get) {
                            if ($state) {
                                $set('maintainer_name',           $get('customer_name'));
                                $set('maintainer_email',          $get('customer_email'));
                                $set('maintainer_phone',          $get('customer_phone'));
                                $set('maintainer_zip',            $get('customer_zip'));
                                $set('maintainer_city',           $get('customer_city'));
                                $set('maintainer_street',         $get('customer_street'));
                                $set('maintainer_street_number',  $get('customer_street_number'));
                            }
                        })
                        ->afterStateUpdated(function (bool $state, Set $set, Get $get) {
                            if ($state) {
                                $set('maintainer_name',           $get('customer_name'));
                                $set('maintainer_email',          $get('customer_email'));
                                $set('maintainer_phone',          $get('customer_phone'));
                                $set('maintainer_zip',            $get('customer_zip'));
                                $set('maintainer_city',           $get('customer_city'));
                                $set('maintainer_street',         $get('customer_street'));
                                $set('maintainer_street_number',  $get('customer_street_number'));
                            } else {
                                $set('maintainer_name', null);
                                $set('maintainer_email', null);
                                $set('maintainer_phone', null);
                                $set('maintainer_zip', null);
                                $set('maintainer_city', null);
                                $set('maintainer_street', null);
                                $set('maintainer_street_number', null);
                            }
                        }),

                    Grid::make(2)->schema([
                        TextInput::make('maintainer_name')->label('Fenntartó neve')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_email')->label('Fenntartó e-mail')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_phone')->label('Fenntartó telefon')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_zip')->label('Irányítószám')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_city')->label('Város')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_street')->label('Utca')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                        TextInput::make('maintainer_street_number')->label('Házszám')
                            ->disabled(fn (Get $get) => $get('maintainer_same_as_customer')),
                    ])->columns(7),
                ]),

            Section::make('Képek és megjegyzés')
                ->schema([
                    FileUpload::make('photo_paths')
                        ->label('Képek (max. 3)')
                        ->multiple()
                        ->maxFiles(3)
                        ->image()
                        ->panelLayout('grid')
                        ->imagePreviewHeight('150')
                        ->disk('private')
                        ->directory('service_reports/tmp')
                        ->preserveFilenames()
                        ->openable()
                        ->downloadable()
                        ->deletable(true)
                        ->reorderable()
                        ->dehydrateStateUsing(fn ($state) =>
                            array_values(
                                array_slice(
                                    array_map(fn ($p) => str_replace('\\', '/', $p), (array) $state),
                                    0,
                                    3
                                )
                            )
                        ),
                    Textarea::make('notes')->label('Megjegyzés')->rows(4),
                ])->extraAttributes(['class' => 'max-w-[560px]']),

            Section::make('Dátum beállítás')
                ->schema([
                    DateTimePicker::make('created_at')
                        ->label('Szerviznapló dátuma (created_at)')
                        ->seconds(false)
                        ->native(false)
                        ->default(fn () => now()),
                ]),

            Forms\Components\Hidden::make('created_by')->default(fn () => auth()->id()),
        ];
    }
}
