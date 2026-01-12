<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Szerviznapló #{{ $report->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        .section { margin-top: 14px; }
    </style>
</head>
<body>
    @php
        // report_type -> magyar címkék
        $typeLabels = [
            'maintenance_warranty'                 => 'Karbantartás (garanciális)',
            'maintenance_non_warranty'             => 'Karbantartás (garancián kívüli)',
            'repair_warranty'                      => 'Javítás (garanciális)',
            'repair_non_warranty'                  => 'Javítás (garancián kívüli)',
            'maintenance_not_covered_by_warranty'  => 'Garanciába nem vehető készülék karbantartás',
            'repair_not_covered_by_warranty'       => 'Garanciába nem vehető készülék javítás',
        ];

        $reportTypeLabel = $typeLabels[$report->report_type] ?? $report->report_type ?? '';
    @endphp

    <h1>Technorgaz</h1>
    <h2>Elektronikus beüzemelési napló - {{ $reportTypeLabel }}</h2>
    <h3>Szerviznapló azonosító: #{{ $report->id }}</h3>

    <div style="border: solid 2px red; padding:5px; width: 50%;">
        <h3>Szerelő adatai:</h3>
        <strong>Cégnév: {{ $report->creator->company->company_name ?? '' }}</strong><br>
        <strong>Szerelő neve: {{ $report->creator->name ?? '' }}</strong><br>
        <strong>Szerelő telefonszáma: {{ $report->creator->partnerDetails->phone ?? '' }}</strong><br>
        Igazolvány száma: {{ $report->creator->partnerDetails->gas_installer_license ?? '' }}<br>
        Igazolvány lejárata: {{ $report->creator->partnerDetails->license_expiration ?? '' }}
    </div>

    <div class="section">
        <strong>Gyári szám:</strong> {{ $report->serial_number }}<br>
        <strong>Dátum:</strong> {{ $report->created_at?->format('Y.m.d H:i') }}
    </div>

    <h3>A jegyzőkönyvben rögzített adatok:</h3>

    @php
        use Illuminate\Support\Facades\Storage;

        $style = isset($hide_style)
            ? ''
            : 'padding:1px 3px 0;border:solid 1px #ccc;font-size:14px;';

        // képek a private diszken, CreateServiceReport alapján: $report->photo_paths
        $encodedImages = [];
        $paths = (array) ($report->photo_paths ?? []);

        if (!empty($paths)) {
            $disk = Storage::disk('private');

            foreach ($paths as $p) {
                $p = trim($p);
                if ($p === '' || ! $disk->exists($p)) {
                    continue;
                }

                $fullPath = $disk->path($p);

                if (is_readable($fullPath)) {
                    $type = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $data = base64_encode(file_get_contents($fullPath));
                    $encodedImages[] = [
                        'type' => $type,
                        'data' => $data,
                    ];
                }
            }
        }
    @endphp

    <table style="width:100%">
        <tbody>
        {{-- Fenntartó / vevő logika ugyanúgy, mint a beüzemelésnél (ha ezek a mezők megvannak a ServiceReport-ben is) --}}
        @if(!empty($report->other_maintainer) && (int)$report->other_maintainer === 1)
            <tr>
                <td style="{{ $style }}" colspan="2">
                    Az ingatlan fenntartója önkormányzat/segélyszervezet.
                </td>
            </tr>
        @endif

        @if(!empty($report->other_maintainer_take_care) && (int)$report->other_maintainer_take_care === 1)
            <tr>
                <td style="{{ $style }}" colspan="2">
                    Fenntartó gondoskodik a karbantartásokról.
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Fenntartó neve:</td>
                <td style="{{ $style }}">{{ $report->maintainer_name ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Cím:</td>
                <td style="{{ $style }}">
                    {{ $report->customer_zip }}. {{ $report->customer_city }}, <br>
                    {{ $report->customer_street }} {{ $report->customer_street_number }}
                    {{ trim(($report->customer_floor ?? '').' '.($report->customer_door ?? '')) }}
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Kapcsolattartó neve:</td>
                <td style="{{ $style }}">{{ $report->maintainer_contact_name ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Kapcsolattartó  telefonszám:</td>
                <td style="{{ $style }}">{{ $report->maintainer_phone ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő e-mail cím:</td>
                <td style="{{ $style }}">{{ $report->maintainer_email ?? '' }}</td>
            </tr>
        @else
            <tr>
                <td style="{{ $style }}">Vevő neve:</td>
                <td style="{{ $style }}">
                    {{ $report->customer_name }}
                    @if(!empty($report->original_name))
                        <br>(Eredeti név: {{ $report->original_name }})
                    @endif
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő e-mail cím:</td>
                <td style="{{ $style }}">
                    @if(!empty($report->customer_email))
                        {{ $report->customer_email }}
                    @else
                        Nincs megadva
                    @endif
                    @if(!empty($report->original_email))
                        <br>(Eredeti e-mail cím: {{ $report->original_email }})
                    @endif
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő címe:</td>
                <td style="{{ $style }}">
                    {{ $report->customer_zip }}. {{ $report->customer_city }}, <br>
                    {{ $report->customer_street }} {{ $report->customer_street_number }}
                    {{ trim(($report->customer_floor ?? '').' '.($report->customer_door ?? '')) }}
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő telefonszám:</td>
                <td style="{{ $style }}">
                    {{ $report->customer_phone }}
                    @if(!empty($report->original_phone))
                        <br>(Eredeti telefonszám: {{ $report->original_phone }})
                    @endif
                </td>
            </tr>
        @endif

        {{-- Műszaki adatok – ha ugyanazok a mezők vannak a ServiceReport-ben is --}}
        <tr>
            <td style="{{ $style }}">Van iszapleválasztó?</td>
            <td style="{{ $style }}">{{ $report->has_sludge_separator ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék típusa:</td>
            <td style="{{ $style }}">{{ $report->product?->name }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék gyári száma:</td>
            <td style="{{ $style }}">{{ $report->serial_number }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Égőnyomás:</td>
            <td style="{{ $style }}">{{ $report->burner_pressure }} mbar</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Füstgáz hőmérséklete:</td>
            <td style="{{ $style }}">{{ $report->flue_gas_temperature }} °C</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Co2 érték:</td>
            <td style="{{ $style }}">{{ $report->co2_value }} %</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Co érték:</td>
            <td style="{{ $style }}">{{ $report->co_value }} ppm</td>
        </tr>
        <tr>
            <td style="{{ $style }}">A készülék EU-s szabvány szélráccsal rendelkezik?</td>
            <td style="{{ $style }}">{{ $report->has_eu_wind_grille ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Bizt. elemek működnek:</td>
            <td style="{{ $style }}">{{ $report->safety_devices_ok ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Füstgáz visszaáramlás:</td>
            <td style="{{ $style }}">nincs</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék gáz tömör:</td>
            <td style="{{ $style }}">{{ $report->gas_tight ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Víznyomás:</td>
            <td style="{{ $style }}">{{ $report->water_pressure }} bar</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Fázishelyes bekötés ellenőrizve:</td>
            <td style="{{ $style }}">{{ $report->phase_correct ? 'igen' : 'nem' }}</td>
        </tr>

        @if(!empty($report->notes ?? $report->comment))
            <tr>
                <td style="{{ $style }}">Megjegyzés, tevékenység leírása, cserélt alkatrész:</td>
                <td style="{{ $style }}">{{ $report->notes ?? $report->comment }}</td>
            </tr>
        @endif

        {{-- Feltöltött képek – td-n belül, normális méretben --}}
        @if(count($encodedImages))
            <tr>
                <td style="{{ $style }}" valign="top">Feltöltött képek:</td>
                <td style="{{ $style }}">
                    {{ count($encodedImages) }} db
                    <hr>
                    @foreach($encodedImages as $img)
                        <div style="width:100%; text-align:center; margin-bottom:12px;">
                            <img
                                src="data:image/{{ $img['type'] }};base64,{{ $img['data'] }}"
                                style="
                                    max-width: 100%;
                                    height: auto;
                                    max-height: 350px;
                                    display: block;
                                    margin: 0 auto;
                                    border:1px solid #ccc;
                                    padding:3px;
                                "
                            >
                        </div>
                    @endforeach
                </td>
            </tr>
        @endif

        @if(!empty($report->competition_number))
            <tr>
                <td style="{{ $style }}">Pályázat száma:</td>
                <td style="{{ $style }}">{{ $report->competition_number }}</td>
            </tr>
        @endif

        <tr>
            <td style="{{ $style }}">Füstgáz elemző típusa:</td>
            <td style="{{ $style }}">{{ $report->creator->partnerDetails->flue_gas_analyzer_type ?? '' }}</td>
        </tr>

        @if(!empty($report->error_description))
            <tr>
                <td style="{{ $style }}">Hiba leírása:</td>
                <td style="{{ $style }}">{{ $report->error_description }}</td>
            </tr>
        @endif

        <tr>
            <td style="{{ $style }}">Szerviz jelentés azonosító (SID):</td>
            <td style="{{ $style }}">{{ $report->sid }}</td>
        </tr>
        </tbody>
    </table>
</body>
</html>
