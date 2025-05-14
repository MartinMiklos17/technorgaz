<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOutputItem extends Model
{
    protected $fillable = [
        'product_output_id',
        'product_id',
        'quantity',
        'price_type',
        'selected_price',
        'discount',
        'is_vat_included',
        'warranty',
        'spare_part_returned',
        'serial_number',
    ];

    protected $casts = [
        'is_vat_included' => 'boolean',
        'warranty' => 'boolean',
        'spare_part_returned' => 'boolean',
    ];

    public function output(): BelongsTo
    {
        return $this->belongsTo(ProductOutput::class, 'product_output_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
