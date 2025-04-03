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
}
