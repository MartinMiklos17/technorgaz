<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'short_description',
        'admin_id',
        'company_id',
    ];

    // Ha szükséges a kapcsolat a User modellel:
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Ha van Company model:
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    protected static function booted()
    {
        static::creating(function ($productCategory) {
            // Ha be van jelentkezve valaki
            if (auth()->check()) {
                $productCategory->user_id = auth()->id();
                $productCategory->company_id = auth()->user()->company_id;
            }
        });
    }
}
