<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerDetails extends Model
{
    use HasFactory;

    // Ha nem követed a Laravel konvenciót (ami szerint a model név singular, de a tábla plural),
    // akkor explicit módon megadhatod a tábla nevét:
    protected $table = 'partner_details';

    protected $fillable = [
        'user_id',
        'company_id',
        'client_take',
        'complete_execution',
        'gas_installer_license',
        'license_expiration',
        'contact_person',
        'phone',
        'location_address',
        'latitude',
        'longitude',
    ];
    protected $casts = [
        'client_take' => 'boolean',
        'complete_execution' => 'boolean',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
