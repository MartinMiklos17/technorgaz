<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductIntakeItem extends Model
{
    protected $fillable = [
        'product_intake_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public function intake(): BelongsTo
    {
        return $this->belongsTo(ProductIntake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
