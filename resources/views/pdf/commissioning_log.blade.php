<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Beüzemelési napló #{{ $log->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        .section { margin-top: 14px; }
    </style>
</head>
<body>
    <h1>Beüzemelési napló</h1>

    <div class="section">
        <strong>Gyári szám:</strong> {{ $log->serial_number }}<br>
        <strong>Dátum:</strong> {{ $log->created_at?->format('Y.m.d H:i') }}
    </div>

    <div class="section">
        <h3>Vevő adatai</h3>
        <table>
            <tr><th>Név</th><td>{{ $log->customer_name }}</td></tr>
            <tr><th>Cím</th><td>{{ $log->customer_zip }} {{ $log->customer_city }}, {{ $log->customer_street }} {{ $log->customer_street_number }}</td></tr>
            <tr><th>Email</th><td>{{ $log->customer_email }}</td></tr>
            <tr><th>Telefon</th><td>{{ $log->customer_phone }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Készülék</h3>
        <table>
            <tr><th>Típus</th><td>{{ $log->product?->name }}</td></tr>
            <tr><th>Égőnyomás</th><td>{{ $log->burner_pressure }}</td></tr>
            <tr><th>Füstgáz hőmérséklet</th><td>{{ $log->flue_gas_temperature }}</td></tr>
            <tr><th>CO₂ érték</th><td>{{ $log->co2_value }}</td></tr>
            <tr><th>CO érték</th><td>{{ $log->co_value }}</td></tr>
            <tr><th>Víznyomás</th><td>{{ $log->water_pressure }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Ellenőrző tételek</h3>
        <table>
            <tr><th>Iszapelválasztó</th><td>{{ $log->has_sludge_separator ? 'Igen' : 'Nem' }}</td></tr>
            <tr><th>EU szélrács</th><td>{{ $log->has_eu_wind_grille ? 'Igen' : 'Nem' }}</td></tr>
            <tr><th>Biztonsági elemek működnek</th><td>{{ $log->safety_devices_ok ? 'Igen' : 'Nem' }}</td></tr>
            <tr><th>Füstgáz visszaáramlás</th><td>{{ $log->flue_gas_backflow ? 'Igen' : 'Nem' }}</td></tr>
            <tr><th>Gáz tömör</th><td>{{ $log->gas_tight ? 'Igen' : 'Nem' }}</td></tr>
        </table>
    </div>
</body>
</html>
