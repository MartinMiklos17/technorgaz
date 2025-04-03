<?php
namespace App\Observers;

use App\Models\ProductOutputItem;
use App\Models\Product;

class ProductOutputItemObserver
{
    public function created(ProductOutputItem $item): void
    {
        Product::where('id', $item->product_id)->decrement('inventory', $item->quantity);
    }

    public function updated(ProductOutputItem $item): void
    {
        $originalQuantity = $item->getOriginal('quantity');
        $originalProductId = $item->getOriginal('product_id');

        // Ha termék változott: a régit visszaadjuk, az újat csökkentjük
        if ($item->product_id !== $originalProductId) {
            Product::where('id', $originalProductId)->increment('inventory', $originalQuantity);
            Product::where('id', $item->product_id)->decrement('inventory', $item->quantity);
        } else {
            // Csak a mennyiség változott
            $diff = $item->quantity - $originalQuantity;
            Product::where('id', $item->product_id)->decrement('inventory', $diff);
        }
    }

    public function deleted(ProductOutputItem $item): void
    {
        Product::where('id', $item->product_id)->increment('inventory', $item->quantity);
    }
}
