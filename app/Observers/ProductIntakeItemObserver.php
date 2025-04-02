<?php

namespace App\Observers;

use App\Models\ProductIntakeItem;
use App\Models\Product;

class ProductIntakeItemObserver
{
    public function created(ProductIntakeItem $item): void
    {
        Product::where('id', $item->product_id)->increment('inventory', $item->quantity);
    }

    public function updated(ProductIntakeItem $item): void
    {
        $originalQuantity = $item->getOriginal('quantity');
        $originalProductId = $item->getOriginal('product_id');

        // Ha termék változott: régi terméktől vonjunk le, újhoz adjunk
        if ($item->product_id !== $originalProductId) {
            Product::where('id', $originalProductId)->decrement('inventory', $originalQuantity);
            Product::where('id', $item->product_id)->increment('inventory', $item->quantity);
        } else {
            // Csak mennyiség változott
            $diff = $item->quantity - $originalQuantity;
            Product::where('id', $item->product_id)->increment('inventory', $diff);
        }
    }

    public function deleted(ProductIntakeItem $item): void
    {
        Product::where('id', $item->product_id)->decrement('inventory', $item->quantity);
    }
}

