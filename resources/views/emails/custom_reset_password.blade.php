<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelszó visszaállítás</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <table align="center" width="600" style="background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);">
        <tr>
            <td align="center">
                <img src="{{ asset('images/technograz-logo.png') }}" alt="Technograz logo" width="200">
            </td>
        </tr>
        <tr>
            <td>
                <h2 style="text-align: center; color: #333;">Jelszó visszaállítás</h2>
                <p>Kedves {{ $notifiable->name }},</p>
                <p>Ezt az e-mailt azért kaptad, mert jelszó-visszaállítási kérelmet nyújtottál be a fiókodhoz.</p>
                <p style="text-align: center;">
                    <a href="{{ $resetUrl }}" style="background-color: #007BFF; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                        Jelszó visszaállítása
                    </a>
                </p>
                <p>Ez a jelszó-visszaállító link <strong>60 perc múlva lejár.</strong></p>
                <p>Ha nem te kezdeményezted a jelszó visszaállítását, kérjük, hagyd figyelmen kívül ezt az e-mailt.</p>
                <p style="margin-top: 20px;">Üdvözlettel,<br><strong>Technograz</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
