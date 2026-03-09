<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>{{ $emailSubject }}</title>
</head>
<body>
    <p>Tisztelt {{ $recipientName }},</p>

    {!! $emailBody !!}

    <p>Üdvözlettel,<br>Technorgáz</p>
</body>
</html>
