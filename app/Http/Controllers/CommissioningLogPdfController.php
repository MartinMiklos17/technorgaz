<?php

namespace App\Http\Controllers;

use App\Models\CommissioningLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CommissioningLogPdfController extends Controller
{
    public function show(Request $request, CommissioningLog $record)
    {
        // jogosultság (ha van policy-d, ez rá engedi a view-t)
        //Gate::authorize('view', $record);

        $path = $record->pdf_path;

        if (! $path || ! Storage::disk('private')->exists($path)) {
            abort(404, 'A PDF nem található.');
        }

        // Inline megjelenítés (nem letöltés)
        $filename = 'commissioning-log-' . $record->id . '.pdf';

        // Laravel Filesystem response inline-ként adja vissza (helyes Content-Type + inline)
        return Storage::disk('private')->response($path, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
