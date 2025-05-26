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
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Support\RawJs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Forms\Components\ZipLookupField;
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

        // Now explicitly fill the form state
        $this->form->fill([
            'email' => $this->email,
        ]);
    }

    public function form(Form $form): Form
    {
        if($this->isAdmin){
            return $form->schema([
                Wizard::make([
                    // 1. lépés: Felhasználói adatok
                    Wizard\Step::make('Felhasználó adatok')
                        ->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->required()
                                ->email()
                                ->default($this->email),
                            TextInput::make('name')
                                ->label(__('Név'))
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
                    Wizard\Step::make('Felhasználó adatok')
                        ->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->default("Martin"),
                            TextInput::make('name')
                                ->label(__('Név'))
                                ->required()
                                ->maxLength(255),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    // 2. lépés: Cégadatok
                    Wizard\Step::make('Cég Adatok')
                        ->schema([
                            Select::make('company_search')
                            ->label('Cég / Adószám kereső')
                            ->searchable()
                            // Ezzel határozzuk meg, milyen találatokat adjon beíráskor:
                            ->getSearchResultsUsing(function (string $search) {
                                // Az $search tartalmazza, amit a user beírt.
                                // Keresünk cégnevet VAGY adószámot tartalmazó rekordokat.
                                return Company::query()
                                    ->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('company_taxnum', 'like', "%{$search}%")
                                    ->limit(10) // korlátozzuk a találatok számát
                                    ->pluck('company_name', 'id');
                            })
                            // Hogyan jelenjen meg a kiválasztott opció szövege a mezőben
                            ->getOptionLabelUsing(function ($value) {
                                $company = Company::find($value);
                                if (! $company) {
                                    return $value;
                                }

                                // Például: "Cégnév (Adószám)"
                                return $company->company_name . ' (' . $company->company_taxnum . ')';
                            })
                            ->reactive() // hogy minden változás után lefusson az afterStateUpdated
                            ->afterStateUpdated(function (callable $set, $state) {
                                // $state = a kiválasztott cég ID-je (vagy null, ha törli)
                                if ($company = Company::find($state)) {
                                    // Töltsük fel a céghez tartozó mezőket
                                    $set('company_name', $company->company_name);
                                    $set('company_taxnum', $company->company_taxnum);
                                    // Pl. cím is:
                                    $set('company_address', $company->company_address);
                                    $set('company_zip', $company->company_zip);
                                    $set('company_city', $company->company_city);
                                    $set('company_country', $company->company_country);
                                } else {
                                    // Ha törlik a kiválasztást, nullázhatjuk a mezőket
                                    $set('company_name', null);
                                    $set('company_taxnum', null);
                                    $set('company_address', null);
                                    $set('company_zip', null);
                                    $set('company_city', null);
                                    $set('company_country', null);
                                    // ...
                                }
                            })
                            // Ezt pl. nem szeretnéd menteni a modelbe, így:
                            ->dehydrated(false) // nem kerül mentésre
                            ->placeholder('Írj be cégnevet vagy adószámot...'),
                            $this->getCompanyNameFormComponent(),
                            $this->getCompanyCountryFormComponent(),
                            $this->getCompanyZipFormComponent(),
                            $this->getCompanyCityFormComponent(),
                            $this->getCompanyAddressFormComponent(),
                            $this->getCompanyTaxnumFormComponent(),
                        ]),
                    // 3. lépés: Térképes címválasztás (Google Maps integráció)
                    Wizard\Step::make('Munkaterület')
                        ->schema([
                            TextInput::make('location_address')
                                ->label(__('Munkaterület Címe')),
                            TextInput::make('latitude')
                                ->label("Szélesség"),
                            TextInput::make('longitude')
                                ->label("Hosszúság"),
                            MapboxField::make('map')
                            ->label(__('Térkép')),/*
                            Placeholder::make('map')
                                ->label(__('Map'))
                                ->content(new HtmlString(view('partials.map')->render()))

                                ->columnSpan('full'),*/
                        ]),
                    //Additional data
                    Wizard\Step::make('További Adatok')
                    ->schema([
                        $this->getClientTakeFormComponent(),
                        $this->getCompleteExecutionFormComponent(),
                        $this->getGasInstallerLicenseFormComponent(),
                        $this->getLicenseExpirationFormComponent(),
                        $this->getContactPersonFormComponent(),
                        $this->getPhoneFormComponent(),
                        $this->getGasAnalyzerTypeFormComponent(),
                        $this->getGasAnalyzerSerialNumberFormComponent(),
                        $this->getGasLicenceFrontImageUploadSectionFormPartnerDetails(),
                        $this->getGasLicenceBackImageUploadSectionFormPartnerDetails(),
                        $this->getGasAnalyzerDocImageUploadSectionFormPartnerDetails(),

                    ]),
                ])
                ->nextAction(
                    fn (Action $action) => $action->label('Következő')
                    ->extraAttributes([
                        'onclick' => 'initMap();',
                    ])
                )
                ->extraAttributes(['class' => 'w-full'])
                ->persistStepInQueryString()
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button
                        type="submit"
                        size="sm"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                        wire:target="
                            register,
                            data.gas_installer_license_front_image,
                            data.gas_installer_license_back_image,
                            data.flue_gas_analyzer_doc_image"
                    >
                        Regisztráció
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
        if (User::where('email', $data['email'])->exists()) {
            Notification::make()
                ->title('Az email cím már használatban van.')
                ->danger()
                ->send();

            return null;
        }
        if($this->isAdmin){
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'company_id' => $this->companyId,
                'is_admin' => $this->isAdmin,
                'account_type'=>'service',
            ]);
        }else{
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'account_type'=>'service',
            ]);
            $existingCompany = Company::firstWhere('company_taxnum', $data['company_taxnum']);

            $company=null;

            if ($existingCompany) {
                // Már létezik egy cég ezzel az adószámmal,
                // használjuk ezt, ne hozzunk létre újat.
                $company = $existingCompany;

                // Opcionálisan: frissíthetjük a meglévő céget az újonnan beírt adatokkal,
                // ha *mindenképpen* mindig az új adatokat akarjuk megőrizni.
                // Ha inkább a régi adatokat hagynád érintetlenül, ezt a részt kihagyhatod.
                /*
                $company->update([
                    'company_name'    => $data['company_name']   ?? $company->company_name,
                    'company_country' => $data['company_country'] ?? $company->company_country,
                    'company_zip'     => $data['company_zip']     ?? $company->company_zip,
                    'company_city'    => $data['company_city']    ?? $company->company_city,
                    'company_address' => $data['company_address'] ?? $company->company_address,
                ]);
                */
            } else {
                // Ha nincs még ilyen adószámú cég, akkor létrehozzuk
                $company = Company::create([
                    'user_id'          => $user->id,
                    'company_name'     => $data['company_name'],
                    'company_country'  => $data['company_country'],
                    'company_zip'      => $data['company_zip'],
                    'company_city'     => $data['company_city'],
                    'company_address'  => $data['company_address'],
                    'company_taxnum'   => $data['company_taxnum'],
                ]);
            }
            // 3) Fájlok átnevezése, ha feltöltöttek valamit
            $timestamp = time(); // másodpercre pontos egyediség
            $userId = $user->id;

            $userDir = "user_{$userId}";

            // Előbb hozd létre, ha nem létezik:
            if (! Storage::disk('partner_documents_upload')->exists($userDir)) {
                Storage::disk('partner_documents_upload')->makeDirectory($userDir);
            }
            // 1) Előlap
            if (!empty($data['gas_installer_license_front_image'])) {
                $oldPath = $data['gas_installer_license_front_image'];
                // Pl. "partner_documents/tmp/IMG_1234.jpg"

                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newName = "user_{$userId}/gas_installer_license_front_image_{$timestamp}.{$extension}";

                // Létrehozzuk a user_{id} almappát és mozgatjuk a fájlt
                Storage::disk('partner_documents_upload')->move($oldPath, $newName);

                // A $data-ban felülírjuk az új elérési utat
                $data['gas_installer_license_front_image'] = $newName;
            }

            // 2) Hátlap
            if (!empty($data['gas_installer_license_back_image'])) {
                $oldPath = $data['gas_installer_license_back_image'];
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newName = "user_{$userId}/gas_installer_license_back_image_{$timestamp}.{$extension}";

                Storage::disk('partner_documents_upload')->move($oldPath, $newName);
                $data['gas_installer_license_back_image'] = $newName;
            }

            // 3) Füstgázmérő
            if (!empty($data['flue_gas_analyzer_doc_image'])) {
                $oldPath = $data['flue_gas_analyzer_doc_image'];
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newName = "user_{$userId}/flue_gas_analyzer_doc_image_{$timestamp}.{$extension}";

                Storage::disk('partner_documents_upload')->move($oldPath, $newName);
                $data['flue_gas_analyzer_doc_image'] = $newName;
            }

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
                'flue_gas_analyzer_type'    => $data['flue_gas_analyzer_type'],
                'flue_gas_analyzer_serial_number'   => $data['flue_gas_analyzer_serial_number'],

                'gas_installer_license_front_image' => $data['gas_installer_license_front_image'] ?? null,
                'gas_installer_license_back_image'  => $data['gas_installer_license_back_image'] ?? null,
                'flue_gas_analyzer_doc_image'       => $data['flue_gas_analyzer_doc_image'] ?? null,
                'account_type'=>'service',
            ]);
            // Frissítsük a felhasználó rekordját, ha a User modelled tartalmaz company_id mezőt
            $user->update(['company_id' => $company->id]);
            $user->update(['partner_details_id' => $partnerDetails->id]);

            //customer create
            $parsed = $this->parseAddressParts($company->company_address);

            \App\Models\Customer::create([
                'billing_name' => $company->company_name,
                'billing_zip' => $company->company_zip,
                'billing_city' => $company->company_city,
                'billing_street' => $parsed['street'],
                'billing_streetnumber' => $parsed['streetnumber'],
                'billing_floor' => $parsed['floor'],
                'billing_door' => $parsed['door'],

                'postal_name' => $company->company_name,
                'postal_zip' => $company->company_zip,
                'postal_city' => $company->company_city,
                'postal_street' => $parsed['street'],
                'postal_streetnumber' => $parsed['streetnumber'],
                'postal_floor' => $parsed['floor'],
                'postal_door' => $parsed['door'],

                'taxnumber' => $company->company_taxnum,
                'contact_name' => $data['contact_person'] ?? null,
                'contact_email' => $data['email'],
                'contact_phone' => $data['phone'] ?? null,
                // 🆕 Új mezők:
                'user_id' => $user->id,
                'partner_details_id' => $partnerDetails->id,
                'account_type'=>'service',
            ]);
            // supplier létrehozása
            \App\Models\Supplier::create([
                'name'         => $company->company_name,
                'zip'          => $company->company_zip,
                'city'         => $company->company_city,
                'street'       => $parsed['street'],
                'streetnumber' => $parsed['streetnumber'],
                'floor'        => $parsed['floor'],
                'door'         => $parsed['door'],
                'taxnum'       => $company->company_taxnum,
                'contact_name' => $data['contact_person'] ?? null,
                'email'        => $data['email'],
                'phone'        => $data['phone'] ?? null,
            ]);
        }

        auth()->login($user);

        // E-mail hitelesítés, ha szükséges
        if (!$user->hasVerifiedEmail()) {
            $notification = app(VerifyEmail::class);
            $notification->url = Filament::getVerifyEmailUrl($user);
            $user->notify($notification);
        }
        // Email értesítés adminoknak új regisztrációról
        $adminUsers = User::where('is_admin', true)->get();
        foreach ($adminUsers as $admin) {
            Mail::to($admin->email)->send(new \App\Mail\AdminNewUserNotification($user));
        }

        return app(RegistrationResponse::class);
    }

    protected function getCompanyNameFormComponent(): Component
    {
        return TextInput::make('company_name')
            ->label(__('Cégnév'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyCountryFormComponent(): Component
    {
        return TextInput::make('company_country')
            ->label(__('Ország'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyZipFormComponent(): Component
    {
        return ZipLookupField::make('company_zip')
            ->required()
            ->cityField('company_city')
            ->label(__('Irsz'))
            ->maxLength(255);
    }

    protected function getCompanyCityFormComponent(): Component
    {
        return TextInput::make('company_city')
            ->label(__('Város'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyAddressFormComponent(): Component
    {
        return TextInput::make('company_address')
            ->label(__('Cím'))
            ->maxLength(255)
            ->required();
    }

    protected function getCompanyTaxnumFormComponent(): TextInput
    {
        return TextInput::make('company_taxnum')
            ->label(__('Adószám'))
            // Pl. Filament 2.x / 3.x mask beállítás
            ->mask('99999999-9-99')
            // A 8-1-2 formátumot regexszel ellenőrizheted
            ->rule('regex:/^\d{8}-\d-\d{2}$/')
            ->maxLength(255)
            ->required();
    }
        protected function getClientTakeFormComponent(): Component
    {
        return Checkbox::make('client_take')

            ->label('Ügyeletet vállal-e')
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
            ->prefix('G/')
            ->label(__('Gázszerelő igazolvány száma'))
            ->mask('99999/9999')
            ->maxLength(255)
            ->required()
            ->formatStateUsing(function ($state) {
                return $state
                    ? Str::of($state)->replace('G/', '') // "G/12345" -> "12345"
                    : null;
            })
            // Amikor mentjük a mezőt (Form->DB), újra elé tesszük a prefixet
            ->dehydrateStateUsing(function ($state) {
                return 'G/' . $state; // "12345" -> "G/12345"
            });
    }

    protected function getLicenseExpirationFormComponent(): Component
    {
        return DatePicker::make('license_expiration')
            ->label(__('Igazolvány lejárata'))
            ->required()
            ->native(false);
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

    protected function getGasAnalyzerSerialNumberFormComponent(): Component
    {
        return TextInput::make('flue_gas_analyzer_serial_number')
            ->label(__('Füstgázelemző sorozatszáma'))
            ->maxLength(50)
            ->required();
    }
    protected function getGasAnalyzerTypeFormComponent(): Component
    {
        return TextInput::make('flue_gas_analyzer_type')
            ->label(__('Füstgázelemző típusa'))
            ->maxLength(50)
            ->required();
    }
    /************************************************************
     * 1) Előlap: gas_installer_license_front_image
     ************************************************************/
    protected function getGasLicenceFrontImageUploadSectionFormPartnerDetails(): Component
    {
        return FileUpload::make('gas_installer_license_front_image')
            ->label('Gázszerelő igazolvány - Előlap')
            // Az a diszk, amit a config/filesystems.php-ban definiáltál,
            // pl. 'partner_documents_upload' => [ 'root' => public_path('uploads/partner_documents'), ... ]
            ->disk('partner_documents_upload')
            ->acceptedFileTypes(['image/*','application/pdf'])
            ->imagePreviewHeight('200')
            ->openable()      // Filament 3.x: "Megnyitás" gomb
            ->downloadable()  // "Letöltés" gomb
            ->previewable()   // Előnézet (képekhez)
            ->deletable(true)
            ->hint('Kép vagy PDF')
            // Ha a felhasználótól függő mappába akarod pakolni
            ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                // Ha Szerkesztésnél ($record) már ismert a user_id
                if ($record && $record->user_id) {
                    return 'user_' . $record->user_id;
                }
                // Ha Új rekordnál a form-on van egy user_id mező
                $formUserId = $get('user_id');
                return $formUserId
                    ? 'user_' . $formUserId
                    : 'tmp'; // Alapesetben "tmp" alkönyvtár
            });
    }

    /************************************************************
     * 2) Hátlap: gas_installer_license_back_image
     ************************************************************/
    protected function getGasLicenceBackImageUploadSectionFormPartnerDetails(): Component
    {
        return FileUpload::make('gas_installer_license_back_image')
            ->label('Gázszerelő igazolvány - Hátlap')
            ->disk('partner_documents_upload')
            ->acceptedFileTypes(['image/*','application/pdf'])
            ->imagePreviewHeight('200')
            ->openable()
            ->downloadable()
            ->previewable()
            ->deletable(true)
            ->hint('Kép vagy PDF')
            ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                if ($record && $record->user_id) {
                    return 'user_' . $record->user_id;
                }
                $formUserId = $get('user_id');
                return $formUserId
                    ? 'user_' . $formUserId
                    : 'tmp';
            });
    }

    /************************************************************
     * 3) Füstgázmérő dok/számla: flue_gas_analyzer_doc_image
     ************************************************************/
    protected function getGasAnalyzerDocImageUploadSectionFormPartnerDetails(): Component
    {
        return FileUpload::make('flue_gas_analyzer_doc_image')
            ->label('Füstgázmérő dokumentum / számla')
            ->disk('partner_documents_upload')
            ->acceptedFileTypes(['image/*','application/pdf'])
            ->imagePreviewHeight('200')
            ->openable()
            ->downloadable()
            ->previewable()
            ->deletable(true)
            ->hint('Kép vagy PDF')
            ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                if ($record && $record->user_id) {
                    return 'user_' . $record->user_id;
                }
                $formUserId = $get('user_id');
                return $formUserId
                    ? 'user_' . $formUserId
                    : 'tmp';
            });
    }
    function parseAddressParts(?string $fullAddress): array
    {
        // Alapértelmezés: üres vagy hibás cím esetén
        $default = [
            'street' => null,
            'streetnumber' => null,
            'floor' => null,
            'door' => null,
        ];

        if (!$fullAddress) {
            return $default;
        }

        // Egyszerű regex: "utca 12/A II/3"
        preg_match('/^(.+?)\s+(\d+[\/\dA-Za-z]*)\s*(.*)$/u', $fullAddress, $matches);

        return [
            'street' => $matches[1] ?? null,
            'streetnumber' => $matches[2] ?? null,
            'floor' => null, // ezt külön lehetne keresni a $matches[3]-ból
            'door' => null,
        ];
    }
}
