<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Leltárív</title>
    <p>
        Dátum:<strong> {{ \Carbon\Carbon::now()->format('Y. m. d.') }}</strong><br>
        Felhasználó:<strong> {{ $userName }}</strong><br>
        Teljes készlet értéke:<strong> {{ number_format($totalInventoryValue, 2, ',', ' ') }} Ft</strong>
    </p>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Leltárív</h2>
    <table>
        <thead>
            <tr>
                <th>Termék neve</th>
                <th>Cikkszám</th>
                <th>Készlet (db)</th>
                <th>Beszerzési ár (Ft)</th>
                <th>Összesen (Ft)</th>
                <th>Leltározott mennyiség</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['item_number'] }}</td>
                <td>{{ $product['inventory'] }}</td>
                <td>{{ number_format($product['purchase_price'], 2, ',', ' ') }}</td>
                <td>{{ number_format($product['total_value'], 2, ',', ' ') }}</td>

                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
