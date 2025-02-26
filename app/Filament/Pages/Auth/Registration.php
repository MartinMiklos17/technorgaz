<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\User;
use App\Models\Invitation;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class Registration extends Register
{
    protected ?string $maxWidth = '2xl';
    public ?string $token = null;
    public ?Invitation $invitation = null;

    public function mount(): void
    {
        $this->token = request()->query('token');

        if ($this->token) {
            // Meghívó ellenőrzése csak akkor, ha van token a kérésben
            $this->invitation = Invitation::where('invitation_token', $this->token)
                ->where('accepted_at', null)
                ->first();

            if (!$this->invitation) {
                redirect()->route('filament.admin.auth.login')
                    ->with('error', 'Érvénytelen vagy lejárt meghívó.');
                return;
            }

            // E-mail mező beállítása a meghívás e-mail címével
            $this->form->fill([
                'email' => $this->invitation->email,
            ]);
        }
    }


    public function form(Form $form): Form
    {
        if ($this->invitation) {
            // Meghívásos regisztrációs űrlap Wizard-al
            return $form->schema([
                Wizard::make([
                    Wizard\Step::make('User Details')
                        ->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->readOnly()
                                ->required(),

                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),

                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                <x-filament::button
                    type="submit"
                    size="sm"
                    wire:submit="register"
                >
                    Register
                </x-filament::button>
                BLADE))),
            ]);
        }

        // Normál regisztrációs űrlap meghívó nélkül
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Contact')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
                Wizard\Step::make('Company Data')
                    ->schema([
                        $this->getCompanyNameFormComponent(),
                        $this->getCompanyCountryFormComponent(),
                        $this->getCompanyZipFormComponent(),
                        $this->getCompanyCityFormComponent(),
                        $this->getCompanyAddressFormComponent(),
                        $this->getCompanyTaxnumFormComponent(),
                    ]),
            ])->submitAction(new HtmlString(Blade::render(<<<BLADE
            <x-filament::button
                type="submit"
                size="sm"
                wire:submit="register"
            >
                Register
            </x-filament::button>
            BLADE))),
        ]);
    }

    //ez kell ahhoz hogy eltuntessuk a default sign up gombot és csak a custom látszódjon
    protected function getFormActions(): array
    {
        return [];
    }


    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        if ($this->invitation) {
            // Meghívással érkező felhasználó regisztrálása
            $user = User::create([
                'name' => $data['name'],
                'email' => $this->invitation->email,
                'password' => $data['password'],
                'company_id' => $this->invitation->company_id,
            ]);

            // Meghívó státusz frissítése
            $this->invitation->update(['accepted_at' => now()]);
        }else{
            // Normál regisztrációs folyamat meghívó nélkül
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Cégadatok létrehozása és hozzárendelése a felhasználóhoz
            $company = Company::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'company_country' => $data['company_country'],
                'company_zip' => $data['company_zip'],
                'company_city' => $data['company_city'],
                'company_address' => $data['company_address'],
                'company_taxnum' => $data['company_taxnum'],
            ]);

            // Cég hozzárendelése a felhasználóhoz
            $user->update(['company_id' => $company->id]);
        }
        // Automatikus bejelentkezés és válasz visszaadása
        auth()->login($user);

        // E-mail hitelesítési folyamat
        if (!$user->hasVerifiedEmail()) {
            $notification = app(VerifyEmail::class);
            $notification->url = Filament::getVerifyEmailUrl($user);
            $user->notify($notification);
        }

        return app(RegistrationResponse::class);
    }

    protected function getCompanyNameFormComponent(): Component
    {
        return TextInput::make('company_name')
            ->label(__('Company Name'))
            ->maxLength(255);
    }

    protected function getCompanyCountryFormComponent(): Component
    {
        return TextInput::make('company_country')
            ->label(__('Country'))
            ->maxLength(255);
    }

    protected function getCompanyZipFormComponent(): Component
    {
        return TextInput::make('company_zip')
            ->label(__('Zip Code'))
            ->maxLength(255);
    }

    protected function getCompanyCityFormComponent(): Component
    {
        return TextInput::make('company_city')
            ->label(__('City'))
            ->maxLength(255);
    }

    protected function getCompanyAddressFormComponent(): Component
    {
        return TextInput::make('company_address')
            ->label(__('Address'))
            ->maxLength(255);
    }

    protected function getCompanyTaxnumFormComponent(): Component
    {
        return TextInput::make('company_taxnum')
            ->label(__('Tax Number'))
            ->maxLength(255);
    }
}
