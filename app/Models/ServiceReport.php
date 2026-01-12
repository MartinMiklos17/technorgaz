<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\CommissioningLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ServiceReport extends Model
{
    protected $table = 'service_reports';

    protected $fillable = [
        'sid',
        'commissioning_log_id',
        'product_id',
        'created_by',
        'created_at',

        'serial_number',
        'report_type',

        // ÚJ MŰSZAKI MEZŐK
        'burner_pressure',
        'flue_gas_temperature',
        'co2_value',
        'co_value',
        'water_pressure',
        'has_sludge_separator',
        'has_eu_wind_grille',
        'safety_devices_ok',
        'flue_gas_backflow',
        'gas_tight',
        'correct_phase_connection',

        'customer_name',
        'customer_zip',
        'customer_city',
        'customer_street',
        'customer_street_number',
        'customer_email',
        'customer_phone',

        'owner_is_different',
        'owner_name',
        'owner_zip',
        'owner_city',
        'owner_street',
        'owner_street_number',
        'owner_email',
        'owner_phone',

        'maintainer_same_as_customer',
        'maintainer_name',
        'maintainer_zip',
        'maintainer_city',
        'maintainer_street',
        'maintainer_street_number',
        'maintainer_email',
        'maintainer_phone',

        'notes',
        'photo_paths',
        'pdf_path',
    ];

    protected $casts = [
        // boolok
        'owner_is_different'          => 'bool',
        'maintainer_same_as_customer' => 'bool',
        'has_sludge_separator'        => 'bool',
        'has_eu_wind_grille'          => 'bool',
        'safety_devices_ok'           => 'bool',
        'flue_gas_backflow'           => 'bool',
        'gas_tight'                   => 'bool',
        'correct_phase_connection'    => 'bool',

        // számok
        'burner_pressure'             => 'int',
        'flue_gas_temperature'        => 'int',
        'co2_value'                   => 'int',
        'co_value'                    => 'int',
        'water_pressure'              => 'int',

        // fájlok
        'photo_paths'                 => 'array',
    ];

    /** Ki hozta létre */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Kapcsolódó beüzemelési napló */
    public function commissioningLog(): BelongsTo
    {
        return $this->belongsTo(CommissioningLog::class);
    }

    /** Készülék (típus) */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Származtatott: garanciás-e */
    public function getIsWarrantyAttribute(): bool
    {
        return in_array($this->report_type, [
            'maintenance_warranty',
            'repair_warranty',
        ], true);
    }

    /** Láthatósági scope */
    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        $u = $user ?? auth()->user();
        if (! $u) return $query;

        $isAdmin = method_exists($u, 'isAdmin') ? $u->isAdmin() : (bool) ($u->is_admin ?? false);
        return $isAdmin ? $query : $query->where('created_by', $u->id);
    }
}
