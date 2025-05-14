<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
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
        'account_type',

        'user_id',
        'partner_details_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partnerDetails()
    {
        return $this->belongsTo(PartnerDetails::class);
    }
}
