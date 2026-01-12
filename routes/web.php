<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventorySheetController;
use App\Http\Controllers\CommissioningLogPdfController;
use App\Http\Controllers\PrivateFileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inventory-sheet/download', [InventorySheetController::class, 'download'])->name('inventory-sheet.download');

Route::middleware(['auth'])->group(function () {
    Route::get('/commissioning-logs/{record}/pdf', [CommissioningLogPdfController::class, 'show'])
        ->name('commissioning-logs.pdf');
});

Route::get('/files/private/{path}', [PrivateFileController::class, 'show'])
    ->where('path', '.*')
    ->middleware(['auth'])   // tegyél ide bármi plusz védelmet (pl. policy)
    ->name('private.file');
