<?php

namespace App\Filament\Pages;

use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\User;
class InviteUser extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Felhasználó meghívása';
    protected static bool $shouldRegisterNavigation = false;
    // Nézetfájl megadása
    protected static string $view = 'filament.pages.invite-user';

    public $email;
    public $is_admin;

    public static function route(): string
    {
        return '/invite-user';
    }

    public static function getSlug(): string
    {
        return 'invite-user';
    }
    public function getHeading(): string
    {
        return 'Új Felhasználó meghívása';
    }
    public function getBreadcrumb(): string
    {
        return 'Új felhasználó meghívása';
    }
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('email')
                ->label('Email cím')
                ->required()
                ->email()
                ->unique(Invitation::class, 'email'), // Biztosítja, hogy az e-mail cím egyedi legyen a meghívók között
            Forms\Components\Select::make('is_admin')
                ->label('Admin jogosultság')
                ->options([
                    0 => 'Nem',
                    1 => 'Igen',
                ])
                ->default(0) // Alapértelmezés szerint "Nem"
                ->required(),

        ];
    }

    public function invite()
    {
        $this->validate([
            'email'    => 'required|email',
            'is_admin' => 'required|boolean',
        ]);

        // Ellenőrizd, hogy létezik-e már felhasználó az adott email címmel
        if (User::where('email', $this->email)->exists()) {
            session()->flash('error', 'Már létezik felhasználó ezzel az email címmel!');
            return;
        }

        // Ellenőrizd, hogy már lett-e meghívó küldve az adott emailre
        if (Invitation::where('email', $this->email)->exists()) {
            session()->flash('error', 'Már lett meghívva ezzel az email címmel!');
            return;
        }

        // Ha az ellenőrzések sikeresek, hozzuk létre a meghívót
        $invitation = Invitation::create([
            'email'            => $this->email,
            'company_id'       => Auth::user()->company_id,
            'is_admin'         => $this->is_admin,
            'invitation_token' => Str::random(32),
        ]);

        Notification::route('mail', $this->email)
            ->notify(new InvitationNotification($invitation->invitation_token));

        $this->reset('email');
        session()->flash('success', 'Meghívó sikeresen elküldve!');
    }

}
