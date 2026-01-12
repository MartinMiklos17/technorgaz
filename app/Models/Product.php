<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // If your table name follows the plural convention ("products"),
    // you don't need to explicitly set $table. Otherwise:
    // protected $table = 'products';

    protected $fillable = [
        'item_number',
        'inventory',
        'name',
        'purchase_price',
        'consumer_price',
        'service_price',
        'retail_price',
        'wholesale_price',
        'handover_price',
        'service_partner_price',
        'description',
        'product_category_id',
        'is_active',
        'show_in_webshop',
        'show_in_spare_parts_list',
        'is_main_device',
        'attached_device_id',
        'photos',
        'datasheets',
        'notes',
        'low_stock_limit',
        'height',
        'width',
        'depth',
        'weight',
    ];

    /**
     * If you store JSON columns (photos, datasheets), let Eloquent cast them as arrays.
     */
    protected $casts = [
        'photos' => 'array',
        'datasheets' => 'array',
        'is_active' => 'boolean',
        'show_in_webshop' => 'boolean',
        'show_in_spare_parts_list' => 'boolean',
        'is_main_device' => 'boolean',
        'attached_device_id' => AsArrayObject::class,
    ];

    /**
     * Relationship to product category (belongsTo).
     */
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Self-referencing relation to the "attached device."
     * This allows us to fetch the product that is attached to this one.
     */
    public function attachedDevices()
    {
        return $this->belongsToMany(
            Product::class,
            table: null,
            foreignPivotKey: null,
            relatedPivotKey: null,
            parentKey: 'id',
            relatedKey: 'id'
        )->whereIn('id', $this->attached_device_id ?? []);
    }

    public function attachedChildren()
    {
        return Product::query()
            ->whereJsonContains('attached_device_id', $this->id);
    }
}
