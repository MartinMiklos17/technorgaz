<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CompanyScope;
class Invitation extends Model
{
    protected $fillable = [
        'email',
        'invitation_token',
        'accepted_at',
        'company_id',
        'is_admin',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope());
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
