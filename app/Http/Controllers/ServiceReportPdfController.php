<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceReportPdfController extends Controller
{
    public function stream(Request $request, ServiceReport $record)
    {
        $pdf = Pdf::loadView('pdf.service_report', ['report' => $record]);

        $filename = 'service-report-' . $record->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function download(Request $request, ServiceReport $record)
    {
        $pdf = Pdf::loadView('pdf.service_report', ['report' => $record]);

        $filename = 'service-report-' . $record->id . '.pdf';

        return $pdf->download($filename);
    }
}
