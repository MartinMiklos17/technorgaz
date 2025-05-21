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
use App\Forms\Schemas\PartnerDetailsFormSchema;
use App\Tables\Schemas\PartnerDetailsTableSchema;

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
                ...PartnerDetailsFormSchema::get()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...PartnerDetailsTableSchema::columns()
            ])
            ->filters([
                ...PartnerDetailsTableSchema::filters()
            ])
            ->actions([
                ...PartnerDetailsTableSchema::actions()
            ])
            ->bulkActions([
                ...PartnerDetailsTableSchema::bulkActions()
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
