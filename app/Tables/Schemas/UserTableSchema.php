<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use App\Models\User;
use App\Models\Company;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
class UserTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('account_type')
                ->label('Fiók típusa')
                ->formatStateUsing(fn ($state) => $state->label()),
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
                ];
    }
    public static function filters(): array
    {
        return [
            SelectFilter::make('name')
                ->label('Név')
                ->options(fn () => User::query()
                    ->select('name')
                    ->distinct()
                    ->pluck('name', 'name')
                    ->filter()
                    ->toArray())
                ->searchable(),

            SelectFilter::make('email')
                ->label('Email')
                ->options(fn () => User::query()
                    ->select('email')
                    ->distinct()
                    ->pluck('email', 'email')
                    ->filter()
                    ->toArray())
                ->searchable(),

            SelectFilter::make('company_id')
                ->label('Kapcsolódó cég')
                ->options(fn () => Company::query()
                    ->orderBy('company_name')
                    ->pluck('company_name', 'id')
                    ->toArray())
                ->searchable(),

            Filter::make('email_verified_at')
                ->label('Email cím hitelesítve')
                ->form([
                    Checkbox::make('value')->label('Csak hitelesített emailek'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when(isset($data['value']) && $data['value'], fn ($q) =>
                        $q->whereNotNull('email_verified_at')
                    )
                ),

            Filter::make('created_at')
                ->label('Létrehozva ettől')
                ->form([
                    DatePicker::make('value')->label('Létrehozva ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('created_at', '>=', $value)
                    )
                ),

            Filter::make('updated_at')
                ->label('Módosítva ettől')
                ->form([
                    DatePicker::make('value')->label('Módosítva ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('updated_at', '>=', $value)
                    )
                ),
        ];
    }
    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make()->label('Részletek'),
            Tables\Actions\EditAction::make()->label('Szerkesztés'),
        ];
    }
    public static function headerActions(): array
    {
        return [
        ];
    }
    public static function bulkActions(): array
    {
        return [
        ];
    }
}
