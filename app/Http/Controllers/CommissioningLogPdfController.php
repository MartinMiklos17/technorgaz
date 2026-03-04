<?php

namespace App\Http\Controllers;

use App\Models\CommissioningLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CommissioningLogPdfController extends Controller
{
    public function show(Request $request, CommissioningLog $record)
    {
        $pdf = Pdf::loadView('pdf.commissioning_log', ['log' => $record]);

        $filename = 'commissioning-log-' . $record->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function download(Request $request, CommissioningLog $record)
    {
        $pdf = Pdf::loadView('pdf.commissioning_log', ['log' => $record]);

        $filename = 'commissioning-log-' . $record->id . '.pdf';

        return $pdf->download($filename);
    }
}
