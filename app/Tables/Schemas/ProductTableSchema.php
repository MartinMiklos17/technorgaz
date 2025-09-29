<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\Action;

class ProductTableSchema
{
    public static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('item_number')
                ->label('Cikkszám')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('name')
                ->label('Név')
                ->searchable()
                ->sortable(),
            Tables\Columns\ToggleColumn::make('is_active')
                ->label('Státusz')
                ->searchable()
                ->sortable(),
            Tables\Columns\ToggleColumn::make('show_in_webshop')
                ->label('Webshop?')
                ->sortable(),
            Tables\Columns\TextColumn::make('inventory')
                ->label('Készlet')
                ->sortable(),
            Tables\Columns\TextColumn::make('productCategory.name')
                ->label('Kategória')
                ->sortable(),
        ];
    }
    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('item_number')
                ->label('Cikkszám')
                ->options(fn () => Product::query()
                    ->select('item_number')
                    ->distinct()
                    ->pluck('item_number', 'item_number')
                    ->filter()
                    ->toArray())
                ->searchable(),

            Tables\Filters\SelectFilter::make('name')
                ->label('Név')
                ->options(fn () => Product::query()
                    ->select('name')
                    ->distinct()
                    ->pluck('name', 'name')
                    ->filter()
                    ->toArray())
                ->searchable(),

            Tables\Filters\SelectFilter::make('product_category_id')
                ->label('Kategória')
                ->options(fn () => ProductCategory::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->searchable(),

            Tables\Filters\Filter::make('is_active')
                ->label('Aktív')
                ->form([
                    Checkbox::make('value')->label('Csak aktívak'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when(isset($data['value']) && $data['value'], fn ($q) =>
                        $q->where('is_active', true)
                    )
                ),

            Tables\Filters\Filter::make('show_in_webshop')
                ->label('Webshopban szerepel')
                ->form([
                    Checkbox::make('value')->label('Webshopban szerepel'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when(isset($data['value']) && $data['value'], fn ($q) =>
                        $q->where('show_in_webshop', true)
                    )
                ),

            Tables\Filters\Filter::make('inventory_range')
                ->label('Készlet (tól-ig)')
                ->form([
                    TextInput::make('min')->numeric()->label('Készlet (tól-ig) Min'),
                    TextInput::make('max')->numeric()->label('Készlet (tól-ig) Max'),
                ])
                ->query(function ($query, $data) {
                    return $query
                        ->when($data['min'] !== null, fn ($q) => $q->where('inventory', '>=', (int)$data['min']))
                        ->when($data['max'] !== null, fn ($q) => $q->where('inventory', '<=', (int)$data['max']));
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
        return [
            Action::make('print_inventory_sheet')
                ->label('Leltárív nyomtatása')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(fn () => redirect()->route('inventory-sheet.download')),
        ];
    }
    public static function bulkActions(): array
    {
        return [
        ];
    }
}
