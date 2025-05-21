<?php
namespace App\Forms\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;
use Filament\Forms\Components\FileUpload;
use App\Models\Company;
use App\Models\User;
use App\Helpers\MapboxHelper;
use App\Forms\Components\MapboxField;
class PartnerDetailsFormSchema
{
    public static function get(): array
    {
        return [

                Section::make('Partner típus')
                ->schema([
                    Forms\Components\Select::make('account_type')
                    ->label('Fiók típusa')
                    ->options(AccountType::options())
                    ->required()
                    ->native(false),
                ])
                ->columns(1),
                Section::make('Adatok')
                ->description('Partner Adatai')
                ->icon('heroicon-m-table-cells')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Felhasználó')
                        ->required()
                        ->options(User::all()->pluck('name', 'id')->toArray())
                        ->searchable(),

                    Forms\Components\Select::make('company_id')
                        ->label('Cég')
                        ->required()
                        ->options(Company::all()->pluck('company_name', 'id')->toArray())
                        ->searchable(),

                    Forms\Components\Toggle::make('client_take')->required()
                        ->label('Ügyeletet vállal?'),
                    Forms\Components\Toggle::make('complete_execution')->required()
                        ->label('Teljes kivitelezés'),

                    Forms\Components\TextInput::make('gas_installer_license')
                        ->label('Gázszerelő engedély')
                        ->maxLength(255)
                        ->default(null),

                    Forms\Components\DatePicker::make('license_expiration')
                    ->label('Engedély lejárata')
                    ->required()
                    ->native(false),

                    Forms\Components\TextInput::make('contact_person')
                        ->label('Kapcsolattartó')
                        ->maxLength(255)
                        ->default(null),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefonszám')
                        ->tel()
                        ->maxLength(255)
                        ->default(null),
                ]),
                Section::make('Helyszín')
                ->icon('heroicon-m-map-pin')
                ->schema([
                    Forms\Components\TextInput::make('location_address')
                        ->label('Cím')
                        ->id('data.location_address')
                        ->label(__('Cím'))
                        ->live()
                        ->readOnly(),
                    Forms\Components\TextInput::make('latitude')
                    ->id('data.latitude')
                        ->label(__('Szélesség'))
                        ->live()
                        ->readOnly(),
                    Forms\Components\TextInput::make('longitude')
                    ->id('data.longitude')
                        ->label(__('Hosszúság'))
                        ->live()
                        ->readOnly(),
                    MapboxField::make('map')
                        ->label(__('Térkép')),
                ]),
                Section::make('Füstgázelemző adatok')
                    ->schema([
                        Forms\Components\TextInput::make('flue_gas_analyzer_type')
                            ->label('Füstgázelemző típusa')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('flue_gas_analyzer_serial_number')
                            ->label('Füstgázelemző sorozatszáma')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Feltöltött Fájlok')
                ->icon('heroicon-m-photo')
                ->schema([
                    FileUpload::make('gas_installer_license_front_image')
                    ->label('Igazolvány előlap')
                    ->disk('partner_documents_upload')         // Az újonnan létrehozott diszk
                    ->visibility('public')
                    ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                        // Opcionálisan alkönyvtár user-hez kötve, pl. "uploads/user_123"
                        if ($record && $record->user_id) {
                            return 'user_' . $record->user_id;
                        }
                        $formUserId = $get('user_id');
                        return $formUserId
                            ? 'user_' . $formUserId
                            : 'tmp'; // fallback
                    })
                    ->acceptedFileTypes(['image/*','application/pdf'])
                    ->imagePreviewHeight('200')
                    ->openable()         // Filament 3.x: engedélyez "megnyitás"
                    ->downloadable()     // engedélyez "letöltés"
                    ->previewable()      // képes előnézet
                    ->deletable()
                    ->hint('Kép vagy PDF'),
                    FileUpload::make('gas_installer_license_back_image')
                        ->moveFiles()
                        ->label('Igazolvány hátlap')
                        ->disk('partner_documents_upload')
                        ->acceptedFileTypes(['image/*','application/pdf'])
                        ->imagePreviewHeight('200')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->deletable(false)
                        ->hint('Kép vagy PDF')
                        ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                            if ($record && $record->user_id) {
                                return 'user_' . $record->user_id;
                            }

                            $formUserId = $get('user_id');
                            return $formUserId
                                ? 'user_' . $formUserId
                                : 'tmp';
                        }),

                    FileUpload::make('flue_gas_analyzer_doc_image')
                        ->moveFiles()
                        ->label('Füstgázmérő dokumentum / számla')
                        ->disk('partner_documents_upload')
                        ->acceptedFileTypes(['image/*','application/pdf'])
                        ->imagePreviewHeight('200')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->deletable(false)
                        ->hint('Kép vagy PDF')
                        ->directory(function (callable $get, ?\App\Models\PartnerDetails $record) {
                            if ($record && $record->user_id) {
                                return 'user_' . $record->user_id;
                            }

                            $formUserId = $get('user_id');
                            return $formUserId
                                ? 'user_' . $formUserId
                                : 'tmp';
                        }),
                    ])
        ];
    }
}
