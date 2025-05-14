<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerDetailsResource\Pages;
use App\Models\PartnerDetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Company;
use App\Models\User;
use App\Helpers\MapboxHelper;
use App\Forms\Components\MapboxField; // Az egyedi mező importálása
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Forms\Components\Livewire;
use App\Livewire\Foo;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Section;
use App\Enums\AccountType;

class PartnerDetailsResource extends Resource
{
    protected static ?string $model = PartnerDetails::class;

    protected static ?string $navigationIcon = 'heroicon-o-face-smile';
    protected static ?string $navigationGroup = 'Partnercégek';
    protected static ?string $pluralModelLabel   = 'Partner Adatok';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        ->required(),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_type')
                ->label('Fiók típusa')
                ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Felhasználó'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.company_name')
                    ->label(__('Cég'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('client_take')
                    ->label(__('Ügyeletet vállal?'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('complete_execution')
                    ->label(__('Teljes kivitelezés'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('gas_installer_license')
                    ->label(__('Gázszerelő engedély'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('license_expiration')
                    ->label(__('Engedély lejárata'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label(__('Kapcsolattartó'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Telefonszám'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('location_address')
                    ->label(__('Cím'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('Szélesség'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('Hosszúság'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Létrehozva'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Frissítve'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('gas_installer_license_front_image')
                    ->label('Ig. előlap')
                    ->formatStateUsing(fn ($state) => $state ? 'Megnyitás' : '-')
                    ->url(fn ($record) => $record->gas_installer_license_front_image
                        ? Storage::disk('partner_documents_upload')->url($record->gas_installer_license_front_image)
                        : null)
                    ->openUrlInNewTab()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gas_installer_license_back_image')
                    ->label('Ig. hátlap')
                    ->formatStateUsing(fn ($state) => $state ? 'Megnyitás' : '-')
                    ->url(fn ($record) => $record->gas_installer_license_back_image
                        ? Storage::disk('partner_documents_upload')->url($record->gas_installer_license_back_image)
                        : null)
                    ->openUrlInNewTab()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('flue_gas_analyzer_doc_image')
                    ->label('Füstgázmérő dok.')
                    ->formatStateUsing(fn ($state) => $state ? 'Megnyitás' : '-')
                    ->url(fn ($record) => $record->flue_gas_analyzer_doc_image
                        ? Storage::disk('partner_documents_upload')->url($record->flue_gas_analyzer_doc_image)
                        : null)
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Részletek'),
                Tables\Actions\EditAction::make()->label('Szerkesztés'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Törlés'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartnerDetails::route('/'),
            'create' => Pages\CreatePartnerDetails::route('/create'),
            'view' => Pages\ViewPartnerDetails::route('/{record}'),
            'edit' => Pages\EditPartnerDetails::route('/{record}/edit'),
        ];
    }
}
