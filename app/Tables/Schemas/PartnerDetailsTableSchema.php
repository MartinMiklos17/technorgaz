<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
class PartnerDetailsTableSchema
{
    public static function columns(): array
    {
        return [
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
                ];
    }
    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('account_type')
                ->label('Fiók típusa')
                ->options(fn () => \App\Enums\AccountType::casesAsLabels()) // ha enum, külön függvény kell hozzá
                ->searchable(),

            Tables\Filters\SelectFilter::make('user_id')
                ->label('Felhasználó')
                ->options(fn () => \App\Models\User::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->searchable(),

            Tables\Filters\SelectFilter::make('company_id')
                ->label('Cég')
                ->options(fn () => \App\Models\Company::query()
                    ->orderBy('company_name')
                    ->pluck('company_name', 'id')
                    ->toArray())
                ->searchable(),

            Tables\Filters\SelectFilter::make('contact_person')
                ->label('Kapcsolattartó')
                ->options(fn () => \App\Models\PartnerDetails::query()
                    ->select('contact_person')
                    ->distinct()
                    ->pluck('contact_person', 'contact_person')
                    ->filter()
                    ->toArray())
                ->searchable(),

            Tables\Filters\Filter::make('client_take')
                ->label('Ügyeletet vállal')
                ->form([
                    \Filament\Forms\Components\Checkbox::make('value')->label('Ügyeletet vállal'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when(isset($data['value']) && $data['value'], fn ($q) =>
                        $q->where('client_take', true)
                    )
                ),

            Tables\Filters\Filter::make('complete_execution')
                ->label('Teljes kivitelezés')
                ->form([
                    \Filament\Forms\Components\Checkbox::make('value')->label('Teljes kivitelezés'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when(isset($data['value']) && $data['value'], fn ($q) =>
                        $q->where('complete_execution', true)
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
            Tables\Actions\DeleteBulkAction::make()->label('Törlés'),
        ];
    }
}
