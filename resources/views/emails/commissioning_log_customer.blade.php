<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Beüzemelési napló</title>
</head>
<body>
    <p>Tisztelt {{ $log->customer_name ?? 'Ügyfelünk' }},</p>

    <p>Csatoltan küldjük a készülék beüzemelési naplóját.</p>

    <p>
        <strong>Gyári szám:</strong> {{ $log->serial_number }}<br>
        <strong>Készülék típusa:</strong> {{ $log->product?->name }}<br>
        <strong>Dátum:</strong> {{ $log->created_at?->format('Y.m.d H:i') }}
    </p>

    <p>Üdvözlettel,<br>Technorgáz</p>
</body>
</html>
