<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOutput extends Model
{
    protected static function booted(): void
    {
        static::deleting(function ($output) {
            $output->items()->get()->each->delete();
        });
    }
    protected $fillable = [
        'customer_id',
        'date',
        'note',
        'payment_method',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductOutputItem::class);
    }
    protected $appends = ['total_quantity', 'total_net'];

    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getTotalNetAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->selected_price);
    }
    public function getTotalDiscountAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            $price = $item->selected_price * $item->quantity;
            return $price * ($item->discount / 100);
        });
    }

    public function getTotalFinalAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            $price = $item->selected_price * $item->quantity;
            $discount = $price * ($item->discount / 100);
            return $price - $discount;
        });
    }
}
