<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductIntake extends Model
{
    protected static function booted(): void
    {
        static::deleting(function ($intake) {
            $intake->items()->get()->each->delete();
        });
    }
    protected $fillable = [
        'supplier_id',
        'date',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductIntakeItem::class);
    }
}
