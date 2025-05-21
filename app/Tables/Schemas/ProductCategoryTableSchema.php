<?php
namespace App\Tables\Schemas;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
class ProductCategoryTableSchema
{
    public static function columns(): array
    {
        return [
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Elnevezés'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('short_description')
                    ->label(__('Rövid Leírás'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Rögzítés Dátuma'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Utolsó Módosítás Dátuma'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ];
    }
    public static function filters(): array
    {
        return [
            Tables\Filters\Filter::make('name')
                ->label('Elnevezés')
                ->form([
                    TextInput::make('value')->label('Elnevezés'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('name', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('short_description')
                ->label('Rövid leírás')
                ->form([
                    TextInput::make('value')->label('Rövid leírás'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->where('short_description', 'like', "%$value%")
                    )
                ),

            Tables\Filters\Filter::make('created_at')
                ->label('Rögzítve ettől')
                ->form([
                    DatePicker::make('value')->label('Rögzítve ettől'),
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['value'] ?? null, fn ($q, $value) =>
                        $q->whereDate('created_at', '>=', $value)
                    )
                ),

            Tables\Filters\Filter::make('updated_at')
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
            Tables\Actions\DeleteBulkAction::make()->label('Törlés'),
        ];
    }
}
