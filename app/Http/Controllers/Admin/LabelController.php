<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Services\Label\LabelRenderService;
use Illuminate\Http\Response;

class LabelController extends Controller
{
    public function __construct()
    {
        // Itt tedd rá azt a middleware-t, ami nálatok az admin/Filament védelem.
        // pl. $this->middleware(['auth']);
    }

    public function getImg1(Label $label, LabelRenderService $service): Response
    {
        $png = $service->renderImg1($label);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function getImg2(Label $label, LabelRenderService $service): Response
    {
        $png = $service->renderImg2($label);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    // Opcionális: PDF (ha kell 1:1 mpdf-es adatlap)
    public function getPdf(Label $label, LabelRenderService $service): Response
    {
        $pdf = $service->renderPdf($label);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="label_' . $label->id . '.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }


}
