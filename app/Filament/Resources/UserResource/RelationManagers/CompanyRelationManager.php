<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Forms\Schemas\CompanyFormSchema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Tables\Schemas\CompanyTableSchema;

class CompanyRelationManager extends RelationManager
{
    protected static string $relationship = 'Company';
    protected static ?string $title = 'Cég Adatok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...CompanyFormSchema::get()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                ...CompanyTableSchema::columns()
            ])
            ->filters([
                ...CompanyTableSchema::filters()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Új Cég')
                ->visible(fn (\Filament\Resources\RelationManagers\RelationManager $livewire) =>
                    ! $livewire->getRelationship()->exists()
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Szerkesztés')
                ->modalSubmitActionLabel('Mentés')
                ->modalHeading('Cég szerkesztése')
                ->modalcancelActionLabel('Mégse'),
                Tables\Actions\DeleteAction::make()->label('Törlés')
                ->modalHeading('Kapcsolt Cég Törlése')
                ->modalDescription('Biztosan törölni szeretné a kiválasztott Cég Adatokat?')
                ->modalcancelActionLabel('Mégse'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Kijelöltek Törlése')
                    ->modalHeading('Kapcsolt Cég Törlése')
                    ->modalDescription('Biztosan törölni szeretné a kiválasztott Cég Adatokat?')
                    ->modalcancelActionLabel('Mégse'),
                ])->label('Törlés') ,
            ]);
    }
}
