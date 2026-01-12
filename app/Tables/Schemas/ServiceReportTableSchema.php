<?php

namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Support\Carbon;

class ServiceReportTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('serial_number')
                ->label('Gyári szám')
                ->searchable()
                ->sortable(),

            TextColumn::make('report_type')
                ->label('Jegyzőkönyv típusa')
                ->formatStateUsing(fn (string $state) => [
                            'maintenance_warranty'                 => 'Karbantartás (garanciális)',
                            'maintenance_non_warranty'             => 'Karbantartás (garancián kívüli)',
                            'repair_warranty'                      => 'Javítás (garanciális)',
                            'repair_non_warranty'                  => 'Javítás (garancián kívüli)',
                            'maintenance_not_covered_by_warranty'  => 'Garanciába nem vehető készülék karbantartás',
                            'repair_not_covered_by_warranty'       => 'Garanciába nem vehető készülék javítás',
                ][$state] ?? $state)
                ->badge()
                ->sortable(),

            TextColumn::make('product.name')
                ->label('Készülék típusa')
                ->sortable(),

            TextColumn::make('customer_name')
                ->label('Ügyfél neve')
                ->searchable(),

            IconColumn::make('warranty_valid')
                ->label('Garancia érvényes?')
                ->boolean()
                ->state(function (ServiceReport $record) {
                    $log = $record->commissioningLog;
                    if (! $log || ! $log->created_at) return false;

                    $base    = Carbon::parse($log->created_at);
                    $cdate   = Carbon::parse($record->created_at ?? now());
                    $win1a   = (clone $base)->addMonthsNoOverflow(10);
                    $win1b   = (clone $base)->addMonthsNoOverflow(13);
                    $win2a   = (clone $base)->addMonthsNoOverflow(22);
                    $win2b   = (clone $base)->addMonthsNoOverflow(25);
                    $warrantyUntil = (clone $base)->addYearsNoOverflow(3);

                    $done = ServiceReport::query()
                        ->where('commissioning_log_id', $log->id)
                        ->where('report_type', 'maintenance_warranty')
                        ->pluck('created_at')
                        ->map(fn ($d) => Carbon::parse($d));

                    $firstInWindow  = $done->contains(fn (Carbon $d) => $d->betweenIncluded($win1a, $win1b));
                    $secondInWindow = $done->contains(fn (Carbon $d) => $d->betweenIncluded($win2a, $win2b));

                    if ($record->report_type === 'maintenance_warranty') {
                        return $cdate->betweenIncluded($win1a, $win1b) || $cdate->betweenIncluded($win2a, $win2b);
                    }
                    if ($record->report_type === 'repair_warranty') {
                        return $cdate->lte($warrantyUntil) && $firstInWindow && $secondInWindow;
                    }
                    return false;
                }),

            TextColumn::make('creator.name')
                ->label('Létrehozta')
                ->description(fn ($record) => $record->creator?->email)
                // Saját, biztonságos kereső: whereHas a creatorra
                ->searchable(query: function (Builder $query, string $search) {
                    $query->whereHas('creator', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                // Ha kell a rendezés is: aktiválhatod. Ha továbbra is gondot okozna, kapcsold ki.
                ->sortable(query: function (Builder $query, string $direction) {
                    $query->orderBy(
                        User::select('name')->whereColumn('users.id', 'service_reports.created_by'),
                        $direction
                    );
                }),

            TextColumn::make('photo_paths')
                ->label('Képek')
                ->state(fn ($record) => is_array($record->photo_paths) ? count($record->photo_paths) . ' db' : '—')
                ->sortable(false)
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('Létrehozva')
                ->dateTime()
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Módosítva')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function filters(): array
    {
        return [
            SelectFilter::make('report_type')
                ->label('Típus')
                ->options([
                    'maintenance_warranty'     => 'Karbantartás (garanciális)',
                    'maintenance_non_warranty' => 'Karbantartás (garancián kívüli)',
                    'repair_warranty'          => 'Javítás (garanciális)',
                    'repair_non_warranty'      => 'Javítás (garancián kívüli)',
                ]),

            SelectFilter::make('product_id')
                ->label('Készülék típus')
                ->relationship(
                    'product', 'name',
                    fn (Builder $q) => $q->where('is_main_device', true)->orderBy('name')
                )
                ->searchable(),

            // ⚠️ Itt volt a hiba: NEM creator_id, hanem created_by a mező
            SelectFilter::make('created_by')
                ->label('Létrehozó')
                ->options(
                    User::query()->orderBy('name')->pluck('name', 'id')->toArray()
                ),

            Filter::make('serial_number')
                ->label('Gyári szám')
                ->form([ TextInput::make('value')->label('Gyári szám') ])
                ->query(fn (Builder $q, array $data) =>
                    $q->when($data['value'] ?? null, fn ($qq, $v) => $qq->where('serial_number', 'like', "%{$v}%"))
                ),

            Filter::make('customer_name')
                ->label('Ügyfél név')
                ->form([ TextInput::make('value')->label('Ügyfél név') ])
                ->query(fn (Builder $q, array $data) =>
                    $q->when($data['value'] ?? null, fn ($qq, $v) => $qq->where('customer_name', 'like', "%{$v}%"))
                ),

            Filter::make('created_at_range')
                ->label('Létrehozva (tól-ig)')
                ->form([
                    DatePicker::make('from')->label('Dátumtól'),
                    DatePicker::make('until')->label('Dátumig'),
                ])
                ->query(function (Builder $q, array $data) {
                    $from  = $data['from'] ?? null;
                    $until = $data['until'] ?? null;
                    $q->when($from,  fn ($qq) => $qq->whereDate('created_at', '>=', $from));
                    $q->when($until, fn ($qq) => $qq->whereDate('created_at', '<=', $until));
                }),

            SelectFilter::make('is_warranty_group')
                ->label('Garancia csoport')
                ->options([
                    'warranty'     => 'Garanciális (bármelyik)',
                    'non_warranty' => 'Garancián kívüli (bármelyik)',
                ])
                ->query(function (Builder $q, array $data) {
                    $v = $data['value'] ?? null;
                    if ($v === 'warranty') {
                        $q->whereIn('report_type', ['maintenance_warranty', 'repair_warranty']);
                    } elseif ($v === 'non_warranty') {
                        $q->whereIn('report_type', ['maintenance_non_warranty', 'repair_non_warranty']);
                    }
                }),
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
        return [];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Törlés')
                    ->modalSubmitActionLabel('Mentés')
                    ->modalHeading('Szerviz jegyzőkönyv törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott jegyzőkönyve(ke)t?')
                    ->modalCancelActionLabel('Mégse'),
            ])->label('Törlés'),
        ];
    }
}
