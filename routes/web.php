<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventorySheetController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inventory-sheet/download', [InventorySheetController::class, 'download'])->name('inventory-sheet.download');
