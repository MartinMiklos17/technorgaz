<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\ToggleFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;
class ProductOutputTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('customer.billing_name')
                    ->label('Vevő neve')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.billing_city')
                    ->label('Város')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Kiadás dátuma')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Tételek száma')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_net')
                    ->label('Nettó összeg')
                    ->money('HUF')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' Ft'),

                Tables\Columns\TextColumn::make('total_discount_amount')
                    ->label('Kedvezmény összege')
                    ->money('HUF')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' Ft'),

                Tables\Columns\TextColumn::make('total_final_amount')
                    ->label('Végösszeg')
                    ->money('HUF')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' Ft'),
                Tables\Columns\TextColumn::make('total_net')
                    ->label('Összes érték (nettó)')
                    ->money('HUF')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' Ft'),
                Tables\Columns\TextColumn::make('product_names')
                    ->label('Termékek')
                    ->getStateUsing(function ($record) {
                        return $record->items
                            ->map(fn ($item) => optional($item->product)->name)
                            ->filter()
                            ->unique()
                            ->implode(', ');
                    })
                    ->wrap()
                    ->limit(60)
                    ->tooltip(fn ($record) =>
                        $record->items
                            ->map(fn ($item) => optional($item->product)->name)
                            ->filter()
                            ->unique()
                            ->implode(', ')
                    ),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Fizetési mód')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Készpénz',
                        'card' => 'Bankkártya',
                        'transfer' => 'Átutalás',
                        'other' => 'Egyéb',
                        default => 'Ismeretlen',
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_vat_included')
                    ->label('Áfás?')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->items->contains('is_vat_included', true);
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->alignCenter()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('warranty')
                    ->label('Garanciás?')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->items->contains('warranty', true);
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->alignCenter()
                    ->toggleable(),

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
            // Dátum intervallum
            Filter::make('date')
                ->form([
                    DatePicker::make('from')->label('Dátumtól'),
                    DatePicker::make('until')->label('Dátumig'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                }),

            // Vevő
            SelectFilter::make('customer_id')
                ->label('Vevő')
                ->options(Customer::query()->orderBy('billing_name')->pluck('billing_name', 'id')->toArray())
                ->searchable(),

            // Termék
            SelectFilter::make('product_id')
                ->label('Termék vagy cikkszám')
                ->options(
                    Product::whereIn('id', function ($q) {
                            $q->select('product_id')->from('product_output_items')->distinct();
                        })
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($product) => [
                            $product->id => "{$product->item_number} - {$product->name}"
                        ])
                        ->toArray()
                )
                ->searchable()
                ->placeholder('Összes termék')
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['value'])) return $query;

                    return $query->whereHas('items', fn ($q) => $q->where('product_id', $data['value']));
                }),


            // Garanciális termékek (toggle)
            Filter::make('warranty')
                ->toggle()
                ->label('Csak garanciális')
                ->query(fn (Builder $query): Builder =>
                    $query->whereHas('items', fn ($q) => $q->where('warranty', true))
                ),

            // Áfás termékek
            Filter::make('vat_yes')
                ->toggle()
                ->label('Csak áfás')
                ->default(false)
                ->query(fn (Builder $query): Builder =>
                    $query->whereHas('items', fn ($q) => $q->where('is_vat_included', true))
                ),

            // Nem áfás termékek
            Filter::make('vat_no')
                ->toggle()
                ->label('Csak nem áfás')
                ->default(false)
                ->query(fn (Builder $query): Builder =>
                    $query->orWhereHas('items', fn ($q) => $q->where('is_vat_included', false))
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
