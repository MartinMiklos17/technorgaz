<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Szerviznapló</title>
</head>
<body>
    <p>Tisztelt Ügyfelünk!</p>

    <p>
        Csatoltan küldjük az alábbi szerviznaplót PDF formátumban:
    </p>

    <ul>
        <li><strong>Típus:</strong> {{ $reportTypeLabel }}</li>
        <li><strong>Gyári szám:</strong> {{ $report->serial_number }}</li>
        <li><strong>Szerviznapló azonosító:</strong> #{{ $report->id }}</li>
        @if(!empty($report->created_at))
            <li><strong>Dátum:</strong> {{ $report->created_at->format('Y.m.d H:i') }}</li>
        @endif
    </ul>

    <p>
        A részletes jegyzőkönyvet a csatolt PDF-ben találja.
    </p>

    <p>
        Üdvözlettel,<br>
        Technorgáz szerviz
    </p>
</body>
</html>
