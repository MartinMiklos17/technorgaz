<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventorySheetController;
use App\Http\Controllers\CommissioningLogPdfController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inventory-sheet/download', [InventorySheetController::class, 'download'])->name('inventory-sheet.download');

Route::middleware(['auth'])->group(function () {
    Route::get('/commissioning-logs/{record}/pdf', [CommissioningLogPdfController::class, 'show'])
        ->name('commissioning-logs.pdf');
});
