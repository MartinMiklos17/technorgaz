<?php
// app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'zip',
        'city',
        'street',
        'streetnumber',
        'floor',
        'door',
        'taxnum',
        'contact_name',
        'email',
        'phone',
    ];
}
