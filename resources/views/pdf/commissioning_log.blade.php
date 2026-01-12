<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Elektronikus beüzemelési napló - jegyzőkönyv #{{ $log->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        .section { margin-top: 14px; }
    </style>
</head>
<body>
    <h1>Technorgaz</h1>
    <h2>Elektronikus beüzemelési napló - jegyzőkönyv</h2>
    <h3>Jegyzőkönyv azonosító: #{{ $log->id }}</h3>

    <div style="border: solid 2px red; padding:5px; width: 50%;">
        <h3>Szerelő adatai:</h3>
        <strong>Cégnév: {{ $log->creator->company->company_name ?? '' }}</strong><br>
        <strong>Szerelő neve: {{ $log->creator->name ?? '' }}</strong><br>
        <strong>Szerelő telefonszáma: {{ $log->creator->partnerDetails->phone ?? '' }}</strong><br>
        Igazolvány száma: {{ $log->creator->partnerDetails->gas_installer_license ?? '' }}<br>
        Igazolvány lejárata: {{ $log->creator->partnerDetails->license_expiration ?? '' }}
    </div>

    <div class="section">
        <strong>Gyári szám:</strong> {{ $log->serial_number }}<br>
        <strong>Dátum:</strong> {{ $log->created_at?->format('Y.m.d H:i') }}
    </div>

    <h3>A jegyzőkönyvben rögzített adatok:</h3>

    @php
        // ugyanaz a style trükk, mint a régi kódban
        $style = isset($hide_style)
            ? ''
            : 'padding:1px 3px 0;border:solid 1px #ccc;font-size:14px;';

        // képek a private diszken, a CreateCommissioningLog alapján: $log->photo_paths
        $encodedImages = [];
        $paths = (array) ($log->photo_paths ?? []);

        if (!empty($paths)) {
            $disk = \Illuminate\Support\Facades\Storage::disk('private');

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
        @if(!empty($log->other_maintainer) && (int)$log->other_maintainer === 1)
            <tr>
                <td style="{{ $style }}" colspan="2">
                    Az ingatlan fenntartója önkormányzat/segélyszervezet.
                </td>
            </tr>
        @endif

        @if(!empty($log->other_maintainer_take_care) && (int)$log->other_maintainer_take_care === 1)
            <tr>
                <td style="{{ $style }}" colspan="2">
                    Fenntartó gondoskodik a karbantartásokról.
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Fenntartó neve:</td>
                <td style="{{ $style }}">{{ $log->maintainer_name ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Cím:</td>
                <td style="{{ $style }}">
                    {{ $log->customer_zip }}. {{ $log->customer_city }}, <br>
                    {{ $log->customer_street }} {{ $log->customer_street_number }}
                    {{ trim(($log->customer_floor ?? '').' '.($log->customer_door ?? '')) }}
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Kapcsolattartó neve:</td>
                <td style="{{ $style }}">{{ $log->maintainer_contact_name ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Kapcsolattartó  telefonszám:</td>
                <td style="{{ $style }}">{{ $log->maintainer_phone ?? '' }}</td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő e-mail cím:</td>
                <td style="{{ $style }}">{{ $log->maintainer_email ?? '' }}</td>
            </tr>
        @else
            <tr>
                <td style="{{ $style }}">Vevő neve:</td>
                <td style="{{ $style }}">
                    {{ $log->customer_name }}
                    @if(!empty($log->original_name))
                        <br>(Eredeti név: {{ $log->original_name }})
                    @endif
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő e-mail cím:</td>
                <td style="{{ $style }}">
                    @if(!empty($log->customer_email))
                        {{ $log->customer_email }}
                    @else
                        Nincs megadva
                    @endif
                    @if(!empty($log->original_email))
                        <br>(Eredeti e-mail cím: {{ $log->original_email }})
                    @endif
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő címe:</td>
                <td style="{{ $style }}">
                    {{ $log->customer_zip }}. {{ $log->customer_city }}, <br>
                    {{ $log->customer_street }} {{ $log->customer_street_number }}
                    {{ trim(($log->customer_floor ?? '').' '.($log->customer_door ?? '')) }}
                </td>
            </tr>
            <tr>
                <td style="{{ $style }}">Vevő telefonszám:</td>
                <td style="{{ $style }}">
                    {{ $log->customer_phone }}
                    @if(!empty($log->original_phone))
                        <br>(Eredeti telefonszám: {{ $log->original_phone }})
                    @endif
                </td>
            </tr>
        @endif

        <tr>
            <td style="{{ $style }}">Van iszapleválasztó?</td>
            <td style="{{ $style }}">{{ $log->has_sludge_separator ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék típusa:</td>
            <td style="{{ $style }}">{{ $log->product?->name }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék gyári száma:</td>
            <td style="{{ $style }}">{{ $log->serial_number }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Égőnyomás:</td>
            <td style="{{ $style }}">{{ $log->burner_pressure }} mbar</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Füstgáz hőmérséklete:</td>
            <td style="{{ $style }}">{{ $log->flue_gas_temperature }} °C</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Co2 érték:</td>
            <td style="{{ $style }}">{{ $log->co2_value }} %</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Co érték:</td>
            <td style="{{ $style }}">{{ $log->co_value }} ppm</td>
        </tr>
        <tr>
            <td style="{{ $style }}">A készülék EU-s szabvány szélráccsal rendelkezik?</td>
            <td style="{{ $style }}">{{ $log->has_eu_wind_grille ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Bizt. elemek működnek:</td>
            <td style="{{ $style }}">{{ $log->safety_devices_ok ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Füstgáz visszaáramlás:</td>
            <td style="{{ $style }}">nincs</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Készülék gáz tömör:</td>
            <td style="{{ $style }}">{{ $log->gas_tight ? 'igen' : 'nem' }}</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Víznyomás:</td>
            <td style="{{ $style }}">{{ $log->water_pressure }} bar</td>
        </tr>
        <tr>
            <td style="{{ $style }}">Fázishelyes bekötés ellenőrizve:</td>
            <td style="{{ $style }}">{{ $log->phase_correct ? 'igen' : 'nem' }}</td>
        </tr>

        @if(!empty($log->notes ?? $log->comment))
            <tr>
                <td style="{{ $style }}">Megjegyzés, tevékenység leírása, cserélt alkatrész:</td>
                <td style="{{ $style }}">{{ $log->notes ?? $log->comment }}</td>
            </tr>
        @endif

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
                                    max-height: 350px; /* képméret korlát */
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


        @if(!empty($log->competition_number))
            <tr>
                <td style="{{ $style }}">Pályázat száma:</td>
                <td style="{{ $style }}">{{ $log->competition_number }}</td>
            </tr>
        @endif

        <tr>
            <td style="{{ $style }}">Füstgáz elemző típusa:</td>
            <td style="{{ $style }}">{{ $log->creator->partnerDetails->flue_gas_analyzer_type ?? '' }}</td>
        </tr>

        @if(!empty($log->error_description))
            <tr>
                <td style="{{ $style }}">Hiba leírása:</td>
                <td style="{{ $style }}">{{ $log->error_description }}</td>
            </tr>
        @endif

        <tr>
            <td style="{{ $style }}">Beüzemelési azonosító:</td>
            <td style="{{ $style }}">{{ $log->commissioning_identifier ?? $log->commissioning_id }}</td>
        </tr>
        </tbody>
    </table>
</body>
</html>
