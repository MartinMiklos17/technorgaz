<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\PartnerDetails;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use App\Models\Invitation;
use App\Forms\Components\MapboxField;
use Filament\Forms\Components\Checkbox;

class Registration extends Register
{
    protected ?string $maxWidth = '7xl';
    public ?bool $isAdmin = false;
    public ?int $companyId = null;

    public ?string $email = null;
    public function mount(): void
    {
        $invitation = Invitation::where('invitation_token', request()->get('token'))->first();
        $this->isAdmin = $invitation?->is_admin ?? false;
        $this->companyId = $invitation?->company_id ?? null;
        $this->email = $invitation?->email ?? null;
    }

    public function form(Form $form): Form
    {
        if($this->isAdmin){
            return $form->schema([
                Wizard::make([
                    // 1. lépés: Felhasználói adatok
                    Wizard\Step::make('User Details')
                        ->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->required()
                                ->email()
                                ->default($this->email)
                                ->readOnly(fn () => $this->email !== null),
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="sm" wire:submit="register">
                        Register
                    </x-filament::button>
                    BLADE
                ))),
            ]);
        }else{
            return $form->schema([
                Wizard::make([
                    // 1. lépés: Felhasználói adatok
                    Wizard\Step::make('User Details')
                        ->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->default("Martin"),
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    // 2. lépés: Cégadatok
                    Wizard\Step::make('Company Data')
                        ->schema([
                            $this->getCompanyNameFormComponent(),
                            $this->getCompanyCountryFormComponent(),
                            $this->getCompanyZipFormComponent(),
                            $this->getCompanyCityFormComponent(),
                            $this->getCompanyAddressFormComponent(),
                            $this->getCompanyTaxnumFormComponent(),
                        ]),
                    // 3. lépés: Térképes címválasztás (Google Maps integráció)
                    Wizard\Step::make('Location')
                        ->schema([
                            TextInput::make('location_address')
                                ->label(__('Location Address')),
                            TextInput::make('latitude'),
                            TextInput::make('longitude'),
                            MapboxField::make('map')
                            ->label(__('Térkép')),/*
                            Placeholder::make('map')
                                ->label(__('Map'))
                                ->content(new HtmlString(view('partials.map')->render()))

                                ->columnSpan('full'),*/
                        ]),
                    //Additional data
                    Wizard\Step::make('Additional Data')
                    ->schema([
                        $this->getClientTakeFormComponent(),
                        $this->getCompleteExecutionFormComponent(),
                        $this->getGasInstallerLicenseFormComponent(),
                        $this->getLicenseExpirationFormComponent(),
                        $this->getContactPersonFormComponent(),
                        $this->getPhoneFormComponent(),
                    ]),
                ])
                ->nextAction(
                    fn (Action $action) => $action->label('Next step')
                    ->extraAttributes([
                        'onclick' => 'initMap();',
                    ])
                )
                ->extraAttributes(['class' => 'w-full'])
                ->persistStepInQueryString()
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="sm" wire:submit="register">
                        Register
                    </x-filament::button>
                    BLADE
                ))),
            ]);
        }
    }

    // Eltünteti az alapértelmezett űrlap akciókat, így csak a custom gomb látszik.
    protected function getFormActions(): array
    {
        return [];
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();
        $user=null;
        if($this->isAdmin){
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'company_id' => $this->companyId,
            ]);
        }else{
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],

            ]);
                // Cégadatok (beleértve a térkép adatait) létrehozása és hozzárendelése a felhasználóhoz
                $company = Company::create([
                    'user_id'          => $user->id,
                    'company_name'     => $data['company_name'],
                    'company_country'  => $data['company_country'],
                    'company_zip'      => $data['company_zip'],
                    'company_city'     => $data['company_city'],
                    'company_address'  => $data['company_address'],
                    'company_taxnum'   => $data['company_taxnum'],
                ]);

                // További adatok hozzáadása a partnerhez
                $partnerDetails = PartnerDetails::create([
                    'user_id'          => $user->id,
                    'company_id'          => $company->id,
                    'client_take'           => $data['client_take'],
                    'complete_execution'    => $data['complete_execution'],
                    'gas_installer_license' => $data['gas_installer_license'],
                    'license_expiration'    => $data['license_expiration'],
                    'contact_person'        => $data['contact_person'],
                    'phone'                 => $data['phone'],
                    'location_address'      => $data['location_address'],
                    'latitude'              => $data['latitude'],
                    'longitude'             => $data['longitude'],
                ]);
                // Frissítsük a felhasználó rekordját, ha a User modelled tartalmaz company_id mezőt
                $user->update(['company_id' => $company->id]);
                $user->update(['partner_details_id' => $partnerDetails->id]);
        }

        auth()->login($user);

        // E-mail hitelesítés, ha szükséges
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
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyCountryFormComponent(): Component
    {
        return TextInput::make('company_country')
            ->label(__('Country'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyZipFormComponent(): Component
    {
        return TextInput::make('company_zip')
            ->label(__('Zip Code'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyCityFormComponent(): Component
    {
        return TextInput::make('company_city')
            ->label(__('City'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyAddressFormComponent(): Component
    {
        return TextInput::make('company_address')
            ->label(__('Address'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyTaxnumFormComponent(): Component
    {
        return TextInput::make('company_taxnum')
            ->label(__('Tax Number'))
            ->maxLength(255)
            ->required();
    }
        protected function getClientTakeFormComponent(): Component
    {
        return Checkbox::make('client_take')

            ->label('Ügyfelet vállal-e')
            ->default(false);
    }

    protected function getCompleteExecutionFormComponent(): Component
    {
        return Checkbox::make('complete_execution')

        ->label('Komplett kivitelezés')
        ->default(false);
    }

    protected function getGasInstallerLicenseFormComponent(): Component
    {
        return TextInput::make('gas_installer_license')
            ->label(__('Gázszerelő igazolvány száma'))
            ->maxLength(255)
            ->required();
    }

    protected function getLicenseExpirationFormComponent(): Component
    {
        return DatePicker::make('license_expiration')
            ->label(__('Igazolvány lejárata'))
            ->required();
    }

    protected function getContactPersonFormComponent(): Component
    {
        return TextInput::make('contact_person')
            ->label(__('Kapcsolattartó'))
            ->maxLength(255)
            ->required();
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label(__('Telefonszám'))
            ->tel() // opcionális, ha telefonszám formátumot szeretnél
            ->maxLength(50)
            ->required();
    }
}
