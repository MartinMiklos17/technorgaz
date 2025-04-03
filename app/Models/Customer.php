<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'zip',
        'city',
        'street',
        'streetnumber',
        'floor',
        'door',

        'billing_name',
        'billing_zip',
        'billing_city',
        'billing_street',
        'billing_streetnumber',
        'billing_floor',
        'billing_door',

        'postal_name',
        'postal_zip',
        'postal_city',
        'postal_street',
        'postal_streetnumber',
        'postal_floor',
        'postal_door',

        'taxnumber',
        'contact_name',
        'contact_email',
        'contact_phone',
    ];
}
