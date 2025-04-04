<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'company_name',
        'company_country',
        'company_zip',
        'company_city',
        'company_address',
        'company_taxnum',
    ];

    public function users()
    {
        return $this->hasMany(User::class,  'company_id');
    }

    public function partnerDetails()
    {
        return $this->hasMany(PartnerDetails::class,  'company_id');
    }
}
