<?php

namespace App\Forms\Schemas;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Illuminate\Validation\Rules\Numeric;
use Illuminate\Support\Facades\Blade;

class ProductOrderFormSchema
{
    public static function get(): array
    {
        return [
            Wizard::make([
                Step::make('Termékek kiválasztása')
                    ->description('Válassza ki a rendelni kívánt termékeket, és adja meg a mennyiségeket. A rendszer ezután elkészíti az összesítést. A "Következő lépés" gombra kattintva az email kiküldés felületre érkezik')
                    ->schema([
                        Repeater::make('items')
                            ->label('Rendelt termékek')
                            ->relationship('items')
                            ->schema([
                                Section::make("Termék kiválasztása")
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Termék')
                                            ->options(Product::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('item_number', $product->item_number);
                                                    $set('net_unit_price', $product->purchase_price);
                                                }

                                                // ✅ Számítás meghívása
                                                \App\Forms\Schemas\ProductOrderFormSchema::calculateTotals($set, $get);
                                            })
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->label('Darabszám')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotals($set, $get))
                                            ->suffix(fn (callable $get) =>
                                                $get('product_id') ? ' / Elérhető: ' .
                                                (Product::find($get('product_id'))?->inventory ?? 0) . ' db' : ''
                                            ),
                                        TextInput::make('item_number')
                                            ->label('Cikkszám')
                                            ->readOnly(),
                                        TextInput::make('net_unit_price')
                                            ->label('Nettó egységár (Ft)')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->readOnly()
                                            ->suffix('Ft')
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotals($set, $get)),
                                        Select::make('vat_percent')
                                            ->label('ÁFA %')
                                            ->options([
                                                0 => '0%',
                                                5 => '5%',
                                                18 => '18%',
                                                27 => '27%',
                                            ])
                                            ->default(27)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotals($set, $get)),
                                    ])->columns(5),
                                Section::make("Összesítés")
                                    ->schema([
                                        TextInput::make('net_total_price')
                                            ->numeric()
                                            ->label('Nettó összesen')
                                            ->suffix('Ft')
                                            ->readonly(),
                                        Hidden::make('gross_unit_price'),

                                        Hidden::make('gross_total_price'),

                                        TextInput::make('vat_amount')
                                            ->numeric()
                                            ->label('ÁFA összesen')
                                            ->suffix('Ft')
                                            ->readonly(),
                                    ])->columns(2),
                            ])
                            ->columns(2)
                            ->addActionLabel('Termék hozzáadása')
                            ->addAction(
                                fn (\Filament\Forms\Components\Actions\Action $action) =>
                                    $action
                                        ->label('➕ Termék hozzáadása')
                                        ->button()
                                        ->color('success') // 'primary', 'success', 'danger', 'gray', stb.
                                        ->size('lg')       // 'sm', 'md', 'lg'
                            )
                            ->required(),
                    ]),
                Step::make('Rendelés összesítése és elküldése')
                    ->schema([
                        DatePicker::make('order_date')
                            ->label('Rendelés dátuma')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('email_to')
                            ->label('E-mail cím')
                            ->email()
                            ->required(),
                        TextArea::make("note"),

                        RichEditor::make('email_body')
                            ->label('E-mail tartalom')
                            ->required()
                            ->disableToolbarButtons(['attachFiles', 'codeBlock']),
                    ])
                ])
            ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                <div wire:loading.remove wire:target="submit">
                    <x-filament::button
                        type="submit"
                        size="sm"
                        color="primary"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                    >
                        Rendelés elküldése!
                    </x-filament::button>
                </div>

                <div wire:loading wire:target="submit">
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            BLADE)))
            ->nextAction(function (callable $get, callable $set) {
                $items = $get('items') ?? [];
                $set('email_body', \App\Forms\Schemas\ProductOrderFormSchema::generateEmailBody($items));
            })
            ->columnSpanFull()
        ];
    }
    public static function calculateTotals(callable $set, callable $get): void
    {
        $quantity = (int) $get('quantity') ?: 0;
        $netUnitPrice = (float) $get('net_unit_price') ?: 0;
        $vatPercent = (int) $get('vat_percent') ?: 0;

        $netTotal = $quantity * $netUnitPrice;
        $grossUnitPrice = $netUnitPrice * (1 + $vatPercent / 100);
        $grossTotal = $grossUnitPrice * $quantity;
        $vatAmount = $grossTotal - $netTotal;

        $set('net_total_price', number_format($netTotal, 2, '.', ''));
        $set('gross_unit_price', number_format($grossUnitPrice, 2, '.', ''));
        $set('gross_total_price', number_format($grossTotal, 2, '.', ''));
        $set('vat_amount', number_format($vatAmount, 2, '.', ''));
    }
    public static function generateEmailBody(array $items): string
    {
        if (empty($items)) {
            return '<p>Nincs megadva tétel.</p>';
        }

        $html = "<p>Tisztelt Partnerünk!</p>
    <p>A következő rendelést adjuk le:</p>
    <ul>";

        foreach ($items as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $name = $product?->name ?? '—';

            $quantity = (int) ($item['quantity'] ?? 0);
            $net = (float) ($item['net_unit_price'] ?? 0);
            $vat = (int) ($item['vat_percent'] ?? 0);

            $netTotal = $net * $quantity;
            $grossUnit = $net * (1 + $vat / 100);
            $grossTotal = $grossUnit * $quantity;
            $vatAmount = $grossTotal - $netTotal;

            $html .= "<li>
                <strong>{$name}</strong><br>
                Cikkszám: {$item['item_number']}<br>
                Mennyiség: {$quantity} db<br>
                Nettó egységár: " . number_format($net, 2, ',', ' ') . " Ft<br>

                ÁFA: {$vat}%<br>
                ÁFA összesen: " . number_format($vatAmount, 2, ',', ' ') . " Ft<br>

            </li>";

        }

        $html .= "</ul><p>Üdvözlettel,<br>Csapatunk</p>";

        return $html;
    }
}
