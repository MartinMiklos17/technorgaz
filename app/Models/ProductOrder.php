<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductOrderItem;

class ProductOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'note',
        'order_date',
        'is_sent',
        'total_net_amount',
        'total_gross_amount',
        'total_vat_amount',
        'total_quantity',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'order_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ProductOrderItem::class);
    }
    public function updateTotals(): void
    {
        $totals = $this->items->reduce(function ($carry, $item) {
            $carry['quantity'] += (int) $item->quantity;
            $carry['net'] += (float) $item->net_total_price;
            $carry['vat'] += (float) $item->vat_amount;
            $carry['gross'] += (float) $item->gross_total_price;
            return $carry;
        }, ['quantity' => 0, 'net' => 0.0, 'vat' => 0.0, 'gross' => 0.0]);

        $this->updateQuietly([
            'total_quantity' => $totals['quantity'],
            'total_net_amount' => $totals['net'],
            'total_vat_amount' => $totals['vat'],
            'total_gross_amount' => $totals['gross'],
        ]);
    }
}
