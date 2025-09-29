<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InventorySheetController extends Controller
{
    public function download()
    {
        $products = Product::all()->map(fn ($product) => [
            'name' => $product->name,
            'item_number' => $product->item_number,
            'inventory' => $product->inventory,
            'purchase_price' => $product->purchase_price,
            'total_value' => $product->purchase_price * $product->inventory,
        ]);

        $totalInventoryValue = $products->sum('total_value');
        $userName = Auth::user()?->name ?? 'Ismeretlen felhasználó';

        $pdf = Pdf::loadView('pdf.inventory-sheet', [
            'products' => $products,
            'totalInventoryValue' => $totalInventoryValue,
            'userName' => $userName,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('leltariv.pdf');
    }
}
