<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissioningLog extends Model
{
    protected $table = 'commissioning_logs';

    protected $fillable = [
        'serial_number',

        'customer_name',
        'customer_zip',
        'customer_city',
        'customer_street',
        'customer_street_number',
        'customer_email',
        'customer_phone',

        'has_sludge_separator',
        'product_id',
        'burner_pressure',
        'flue_gas_temperature',
        'co2_value',
        'co_value',
        'has_eu_wind_grille',
        'safety_devices_ok',
        'flue_gas_backflow',
        'gas_tight',
        'water_pressure',

        'pdf_path',
        'created_by',
    ];

    protected $casts = [
        'has_sludge_separator' => 'bool',
        'has_eu_wind_grille'   => 'bool',
        'safety_devices_ok'    => 'bool',
        'flue_gas_backflow'    => 'bool',
        'gas_tight'            => 'bool',
        'burner_pressure'      => 'int',
        'flue_gas_temperature' => 'int',
        'co2_value'            => 'int',
        'co_value'             => 'int',
        'water_pressure'       => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Gyári szám gyors kereséséhez hasznos kis scope */
    public function scopeBySerial($query, string $serial)
    {
        return $query->where('serial_number', $serial);
    }
}
