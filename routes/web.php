<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventorySheetController;
use App\Http\Controllers\CommissioningLogPdfController;
use App\Http\Controllers\ServiceReportPdfController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\Admin\LabelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inventory-sheet/download', [InventorySheetController::class, 'download'])->name('inventory-sheet.download');


Route::middleware(['auth'])->group(function () {
    Route::get('/commissioning-logs/{record}/pdf', [CommissioningLogPdfController::class, 'show'])
        ->name('commissioning-logs.pdf');

    Route::get('/commissioning-logs/{record}/pdf/download', [CommissioningLogPdfController::class, 'download'])
        ->name('commissioning-logs.pdf.download');

    Route::get('/service-reports/{record}/pdf', [ServiceReportPdfController::class, 'stream'])
        ->name('service-reports.pdf');

    Route::get('/service-reports/{record}/pdf/download', [ServiceReportPdfController::class, 'download'])
        ->name('service-reports.pdf.download');
});

Route::get('/files/private/{path}', [PrivateFileController::class, 'show'])
    ->where('path', '.*')
    ->middleware(['auth'])   // tegyél ide bármi plusz védelmet (pl. policy)
    ->name('private.file');

//labels
Route::prefix('admin/label')->group(function () {
    Route::get('get_img_1/{label}', [LabelController::class, 'getImg1'])->name('admin.label.get_img_1');
    Route::get('get_img_2/{label}', [LabelController::class, 'getImg2'])->name('admin.label.get_img_2');
        Route::get('get_pdf/{label}', [LabelController::class, 'getPdf'])
        ->name('admin.label.get_pdf');
});
