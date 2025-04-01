<?php
namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class UsersRelationManager extends RelationManager
{
    // A kapcsolódó Eloquent-metódus neve a Company modelben
    protected static string $relationship = 'users';

    // Pl. a rekord "címe" a user.name lesz a Filament számára
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Felhasználók Listája';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // A User űrlap mezői: pl. név, email, stb.
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),

                // Ha van pl. gas_installer_license mező a usernél:
                // Forms\Components\TextInput::make('gas_installer_license'),
                // ...stb.
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Név'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.company_name')
                    ->searchable()
                    ->label('Kapcsolódó Cég Név'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Email Cím Hitelesítve'),
                Tables\Columns\ToggleColumn::make('is_admin')
                    ->label('Admin Jogosultsága van?')
                    ->sortable()
                    ->disabled(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Részletek'),
                Tables\Actions\EditAction::make()->label('Szerkesztés'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
