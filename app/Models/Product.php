<?php


namespace App\Models;

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
        'show_in_main_carousel',
        'show_in_webshop',
        'has_electronic_installation_log',
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
        'show_in_main_carousel' => 'boolean',
        'show_in_webshop' => 'boolean',
        'has_electronic_installation_log' => 'boolean',
        'show_in_spare_parts_list' => 'boolean',
        'is_main_device' => 'boolean',
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
    public function attachedDevice()
    {
        return $this->belongsTo(Product::class, 'attached_device_id');
    }

    /**
     * Optional: If you want to list all products that attach to this product,
     * you can define a reverse hasMany relationship:
     */
    public function attachedChildren()
    {
        return $this->hasMany(Product::class, 'attached_device_id');
    }
}
