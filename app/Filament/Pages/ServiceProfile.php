<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PartnerDetails;
use App\Models\Company;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\Action;

class ServiceProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.service-profile';
    protected static ?string $navigationGroup='Profilom';
    protected static ?string $title = 'Profilom';
    protected static ?string $navigationLabel = 'Profil';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = $this->getData();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->is_service_partner;
    }

    public function getFormActions(): array
    {
        return [
            new HtmlString(Blade::render(<<<'BLADE'
                <div wire:loading.remove wire:target="submit">
                    <x-filament::button
                        type="submit"
                        size="sm"
                        color="primary"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                    >
                        Mentés!
                    </x-filament::button>
                </div>

                <div wire:loading wire:target="submit">
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            BLADE))
        ];
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Felhasználói adatok')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Név')
                            ->required(),
                        TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),
                    ]),

                Section::make('Partner adatok')
                    ->schema([
                        TextInput::make('partnerDetails.contact_person')
                            ->label('Kapcsolattartó'),
                        TextInput::make('partnerDetails.phone')
                            ->label('Telefonszám'),
                        TextInput::make('partnerDetails.location_address')
                            ->label('Telephely címe'),
                        TextInput::make('partnerDetails.latitude')
                            ->label('Szélesség'),
                        TextInput::make('partnerDetails.longitude')
                            ->label('Hosszúság'),
                        TextInput::make('partnerDetails.gas_installer_license')
                            ->label('Gázszerelő igazolvány'),
                        Forms\Components\DatePicker::make('partnerDetails.license_expiration')
                            ->label('Igazolvány lejárata')
                            ->native(false),
                        Forms\Components\Toggle::make('partnerDetails.client_take')
                            ->label('Ügyeletet vállal?'),
                        Forms\Components\Toggle::make('partnerDetails.complete_execution')
                            ->label('Teljes kivitelezés'),
                        TextInput::make('partnerDetails.flue_gas_analyzer_type')
                            ->label('Füstgázelemző típusa'),
                        TextInput::make('partnerDetails.flue_gas_analyzer_serial_number')
                            ->label('Füstgázelemző sorozatszám'),

                        Forms\Components\FileUpload::make('partnerDetails.gas_installer_license_front_image')
                            ->label('Igazolvány előlap')
                            ->disk('partner_documents_upload')
                            ->directory(fn () => 'user_' . auth()->id())
                            ->visibility('public')
                            //->previewable()
                            ->openable()
                            ->downloadable()
                            ->nullable()
                            ->extraAttributes([
                                'wire:loading.attr' => 'disabled',
                                'wire:target' => 'data.partnerDetails.gas_installer_license_front_image',
                            ]),
                            //->preserveFilenames(),

                        Forms\Components\FileUpload::make('partnerDetails.gas_installer_license_back_image')
                            ->label('Igazolvány hátlap')
                            ->disk('partner_documents_upload')
                            ->visibility('public')
                            ->directory(fn () => 'user_' . auth()->id())
                            //->previewable()
                            ->openable()
                            ->downloadable()
                            ->extraAttributes([
                                'wire:loading.attr' => 'disabled',
                                'wire:target' => 'data.partnerDetails.gas_installer_license_back_image',
                            ]),
                        Forms\Components\FileUpload::make('partnerDetails.flue_gas_analyzer_doc_image')
                            ->label('Füstgázmérő dokumentum')
                            ->disk('partner_documents_upload')
                            ->visibility('public')
                            ->directory(fn () => 'user_' . auth()->id())
                            //->previewable()
                            ->openable()
                            ->downloadable()
                            ->extraAttributes([
                                'wire:loading.attr' => 'disabled',
                                'wire:target' => 'data.partnerDetails.flue_gas_analyzer_doc_image',
                            ]),
                    ]),

                Section::make('Cégadatok')
                    ->schema([
                        TextInput::make('company.company_name')->label('Cégnév'),
                        TextInput::make('company.company_taxnum')->label('Adószám'),
                        TextInput::make('company.company_address')->label('Cím'),
                        TextInput::make('company.company_city')->label('Város'),
                        TextInput::make('company.company_zip')->label('Irányítószám'),
                    ]),
            ])
            ->statePath('data');
    }

    public function getData(): array
    {
        $user = Auth::user();

        return [
            'user' => $user->only(['name', 'email']),
            'partnerDetails' => $user->partnerDetails
                ? [
                    'contact_person' => $user->partnerDetails->contact_person,
                    'phone' => $user->partnerDetails->phone,
                    'location_address' => $user->partnerDetails->location_address,
                    'latitude' => $user->partnerDetails->latitude,
                    'longitude' => $user->partnerDetails->longitude,
                    'gas_installer_license' => $user->partnerDetails->gas_installer_license,
                    'license_expiration' => $user->partnerDetails->license_expiration,
                    'client_take' => $user->partnerDetails->client_take,
                    'complete_execution' => $user->partnerDetails->complete_execution,
                    'flue_gas_analyzer_type' => $user->partnerDetails->flue_gas_analyzer_type,
                    'flue_gas_analyzer_serial_number' => $user->partnerDetails->flue_gas_analyzer_serial_number,

                    'gas_installer_license_front_image' => $user->partnerDetails->gas_installer_license_front_image
                        ? [$user->partnerDetails->gas_installer_license_front_image]
                        : [],

                    'gas_installer_license_back_image' => $user->partnerDetails->gas_installer_license_back_image
                        ? [$user->partnerDetails->gas_installer_license_back_image]
                        : [],

                    'flue_gas_analyzer_doc_image' => $user->partnerDetails->flue_gas_analyzer_doc_image
                        ? [$user->partnerDetails->flue_gas_analyzer_doc_image]
                        : [],
                ]
                : [],
            'company' => $user->company?->only([
                'company_name',
                'company_taxnum',
                'company_address',
                'company_city',
                'company_zip',
            ]) ?? [],
        ];
    }

    public function save(): void
    {
        $user = Auth::user();

        $user->update([
            'name' => $this->data['user']['name'],
        ]);

        $gas_installer_license_front_image=$this->extractPath($this->data['partnerDetails']['gas_installer_license_front_image'] ?? null, 'gas_installer_license_front_image');
        $gas_installer_license_back_image=$this->extractPath($this->data['partnerDetails']['gas_installer_license_back_image'] ?? null, 'gas_installer_license_back_image');
        $flue_gas_analyzer_doc_image=$this->extractPath($this->data['partnerDetails']['flue_gas_analyzer_doc_image'] ?? null, 'flue_gas_analyzer_doc_image');

      //  $this->data['partnerDetails']['gas_installer_license_front_image'] =  $this->extractPath($this->data['partnerDetails']['gas_installer_license_front_image'] ?? null, 'gas_installer_license_front_image');
        //$this->data['partnerDetails']['gas_installer_license_back_image'] = $this->extractPath($this->data['partnerDetails']['gas_installer_license_back_image'] ?? null, 'gas_installer_license_back_image');
        //$this->data['partnerDetails']['flue_gas_analyzer_doc_image'] = $this->extractPath($this->data['partnerDetails']['flue_gas_analyzer_doc_image'] ?? null, 'flue_gas_analyzer_doc_image');



        if ($user->partnerDetails) {
            $user->partnerDetails->update([
                'contact_person' => $this->data['partnerDetails']['contact_person'] ?? null,
                'phone' => $this->data['partnerDetails']['phone'] ?? null,
                'location_address' => $this->data['partnerDetails']['location_address'] ?? null,
                'latitude' => $this->data['partnerDetails']['latitude'] ?? null,
                'longitude' => $this->data['partnerDetails']['longitude'] ?? null,
                'gas_installer_license' => $this->data['partnerDetails']['gas_installer_license'] ?? null,
                'license_expiration' => $this->data['partnerDetails']['license_expiration'] ?? null,
                'client_take' => $this->data['partnerDetails']['client_take'] ?? false,
                'complete_execution' => $this->data['partnerDetails']['complete_execution'] ?? false,
                'flue_gas_analyzer_type' => $this->data['partnerDetails']['flue_gas_analyzer_type'] ?? null,
                'flue_gas_analyzer_serial_number' => $this->data['partnerDetails']['flue_gas_analyzer_serial_number'] ?? null,

                //'gas_installer_license_front_image' => $this->data['partnerDetails']['gas_installer_license_front_image'] ?? null,
                //'gas_installer_license_back_image' => $this->data['partnerDetails']['gas_installer_license_back_image'] ?? null,
                //'flue_gas_analyzer_doc_image' => $this->data['partnerDetails']['flue_gas_analyzer_doc_image'] ?? null,
                'gas_installer_license_front_image' => $gas_installer_license_front_image ?? null,
                'gas_installer_license_back_image' => $gas_installer_license_back_image ?? null,
                'flue_gas_analyzer_doc_image' => $flue_gas_analyzer_doc_image ?? null,
            ]);
        }
        if ($user->company) {
            $user->company->update([
                'company_name' => $this->data['company']['company_name'] ?? null,
                'company_taxnum' => $this->data['company']['company_taxnum'] ?? null,
                'company_address' => $this->data['company']['company_address'] ?? null,
                'company_city' => $this->data['company']['company_city'] ?? null,
                'company_zip' => $this->data['company']['company_zip'] ?? null,
            ]);
        }

        Notification::make()
        ->title('Profil mentve')
        ->success()
        ->body('A módosítások sikeresen elmentve.')
        ->send();
    }

    public function updated($property, $value): void
    {
        $fileFieldPaths = [
            'data.partnerDetails.gas_installer_license_front_image',
            'data.partnerDetails.gas_installer_license_back_image',
            'data.partnerDetails.flue_gas_analyzer_doc_image',
        ];

        foreach ($fileFieldPaths as $basePath) {
            if (Str::startsWith($property, $basePath)) {
                // Futtassuk újra az extractPath-et és tegyük vissza tömbként
                $singlePath = $this->extractPath(data_get($this, $basePath), Str::afterLast($basePath, '.'));

                if ($singlePath) {
                    data_set($this, $basePath, [$singlePath]);
                }

                break;
            }
        }
    }

    protected function extractPath($value, string $fieldName): ?string
    {
        // Ha üres, térj vissza null-lal
        if (blank($value)) {
            return null;
        }

        // Ha egy darab string JSON-nak tűnik, próbáljuk meg dekódolni
        if (is_string($value) && Str::startsWith($value, '{')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $innerValue = reset($decoded);
                if (is_string($innerValue)) {
                    return $innerValue;
                }
            }
        }

        if (is_array($value)) {
            $first = reset($value);

            // Ha az első elem is string és JSON, dekódoljuk újra
            if (is_string($first) && Str::startsWith($first, '{')) {
                $decoded = json_decode($first, true);
                if (is_array($decoded)) {
                    $innerValue = reset($decoded);

                    if (is_array($innerValue)) {
                        return reset($innerValue);
                    }

                    if (is_string($innerValue)) {
                        return $innerValue;
                    }
                }
            }

            // Ha TemporaryUploadedFile objektum
            if ($first instanceof TemporaryUploadedFile) {
                $userId = Auth::id() ?? 'tmp';
                $extension = $first->getClientOriginalExtension();
                $timestamp = now()->timestamp;

                $filename = "{$fieldName}_{$timestamp}." . $extension;
                $path = "user_{$userId}/{$filename}";

                $first->storeAs("user_{$userId}", $filename, 'partner_documents_upload');

                return $path;
            }

            // Ha sima string (pl. mentett fájl elérési útja)
            if (is_string($first)) {
                return $first;
            }
        }

        // Ha maga a value egy sima string
        if (is_string($value)) {
            return $value;
        }

        return null;
    }

}
