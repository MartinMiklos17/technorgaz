<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Supplier;
use App\Models\Product;
class ProductIntakeTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Beszállító')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label('Dátum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Tételek száma')
                    ->counts('items') // auto relationship count
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_list')
                    ->label('Termékek')
                    ->getStateUsing(function ($record) {
                        return $record->items
                            ->map(fn ($item) => optional($item->product)->name)
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->items
                        ->map(fn ($item) => optional($item->product)->name)
                        ->filter()
                        ->implode(', ')
                    ),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Összes érték (nettó)')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum(fn ($item) => $item->quantity * $item->unit_price);
                    })
                    ->money('HUF') // vagy 'EUR', ha úgy használod
                    ->sortable(),

                Tables\Columns\TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->note)
                    ->wrap(),

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
            // Dátum -tól -ig
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

            SelectFilter::make('product_id')
                ->label('Termék vagy cikkszám')
                ->options(
                    Product::whereIn('id', function ($query) {
                            $query->select('product_id')
                                ->from('product_intake_items')
                                ->distinct();
                        })
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($product) => [
                            $product->id => "{$product->item_number} - {$product->name}"
                        ])
                        ->toArray()
                )
                ->placeholder('Összes termék')
                ->searchable()
                ->query(function (Builder $query, array $data): Builder {
                    $productId = $data['value'] ?? null;
                    if ($productId === null) {
                        return $query;
                    }

                    return $query->whereHas('items', fn ($q) => $q->where('product_id', $productId));
                }),


            // Beszállító neve alapján
            SelectFilter::make('supplier_id')
                ->label('Beszállító')
                ->options(Supplier::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),
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
