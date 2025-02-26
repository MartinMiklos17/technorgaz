<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    protected $invitationToken;

    public function __construct($invitationToken)
    {
        $this->invitationToken = $invitationToken;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Meghívás a rendszerbe')
            ->line('Meghívást kaptál a regisztrációra.')
            ->action(
                'Regisztráció',
                url("/admin/register?token={$this->invitationToken}")
            )
            ->line('A meghívó link korlátozott ideig érvényes.');
    }
}

