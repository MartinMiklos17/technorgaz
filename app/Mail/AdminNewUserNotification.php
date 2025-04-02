<?php

// app/Mail/AdminNewUserNotification.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AdminNewUserNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $userUrl = route('filament.admin.resources.users.edit', ['record' => $this->user->id]);

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Új felhasználó regisztrált')
            ->html("
                <h2>Új felhasználó regisztrált</h2>
                <p>Név: {$this->user->name}</p>
                <p>Email: {$this->user->email}</p>
                <p><a href=\"{$userUrl}\">Felhasználó adatlapja</a></p>
            ");
    }
}
