<?php

namespace App\Filament\Pages;

use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteUser extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Felhasználó meghívása';
    protected static bool $shouldRegisterNavigation = false;
    // Nézetfájl megadása
    protected static string $view = 'filament.pages.invite-user';

    public $email;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('email')
                ->label('Email cím')
                ->required()
                ->email()
                ->unique(Invitation::class, 'email'), // Biztosítja, hogy az e-mail cím egyedi legyen a meghívók között
        ];
    }

    public function invite()
    {
        $this->validate([
            'email' => 'required|email|unique:invitations,email',
        ]);

        $invitation = Invitation::create([
            'email' => $this->email,
            'invitation_token' => Str::random(32),
        ]);

        Notification::route('mail', $this->email)
            ->notify(new InvitationNotification($invitation->invitation_token));

        $this->reset('email');
        session()->flash('success', 'Meghívó sikeresen elküldve!');
    }
}
