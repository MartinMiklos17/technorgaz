<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Enums\AccountType;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'company_id',
        'partner_details_id',
        'account_type',
    ];
    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->account_type !== null) {
                // Kapcsolt modellek frissítése
                $user->customer?->updateQuietly([
                    'account_type' => $user->account_type,
                ]);

                $user->partnerDetails?->updateQuietly([
                    'account_type' => $user->account_type,
                ]);

                // Csak akkor állítjuk be a flaget, ha service partner
                $user->updateQuietly([
                    'is_service_partner' => $user->account_type === AccountType::ServicePartner->value,
                ]);
            }
        });
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'account_type' => \App\Enums\AccountType::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function partnerDetails()
    {
        return $this->hasOne(PartnerDetails::class);
    }
    public function sendEmailVerificationNotification()
	{
		$this->notify(new CustomVerifyEmail);
	}
    public function preferredLocale() {
        return $this->locale;
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
