<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrivateFileController extends Controller
{
    public function show(Request $request, string $path)
    {
        // normalizálás
        $path = ltrim(str_replace('\\', '/', $path), '/');

        // Csak a service_reports és commissioning_logs könyvtárakat engedjük
        if (! str_starts_with($path, 'service_reports/') &&
            ! str_starts_with($path, 'commissioning_logs/')) {
            abort(404);
        }

        $disk = Storage::disk('private');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $name = basename($path);

        // Ha ?download=1 van a queryben → letöltés
        if ($request->boolean('download')) {
            return $disk->download($path, $name);
        }

        // Inline megjelenítés (pl. Filament preview/openable)
        return $disk->response($path, $name, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }
}
