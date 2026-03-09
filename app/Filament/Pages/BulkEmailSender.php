<?php

namespace App\Filament\Pages;

use App\Enums\AccountType;
use App\Mail\BulkMessage;
use App\Models\Customer;
use App\Models\PartnerDetails;
use App\Tables\Schemas\CustomerTableSchema;
use App\Tables\Schemas\PartnerDetailsTableSchema;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkEmailSender extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $title = 'Tömeges e-mail küldés';
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationLabel = 'Tömeges küldés';
    protected static string $view = 'filament.pages.bulk-email-sender';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public string $recipient_type = 'customer';
    public ?string $partner_account_type = null;
    public string $email_field = 'contact_email';
    public ?string $subject = null;
    public ?string $body = null;
    public bool $test_mode = false;
    public ?string $test_email = null;

    public function updatedRecipientType(): void
    {
        $this->partner_account_type = null;
        $this->email_field = $this->recipient_type === 'customer' ? 'contact_email' : 'user_email';
        $this->resetTable();
    }

    public function updatedPartnerAccountType(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        if ($this->recipient_type === 'partner') {
            return $this->partnerTable($table);
        }

        return $this->customerTable($table);
    }

    protected function customerTable(Table $table): Table
    {
        return $table
            ->query(Customer::query()->whereNotNull('contact_email')->where('contact_email', '!=', ''))
            ->columns(CustomerTableSchema::columns())
            ->filters(CustomerTableSchema::filters())
            ->selectable()
            ->defaultSort('billing_name');
    }

    protected function partnerTable(Table $table): Table
    {
        $query = PartnerDetails::query()->whereHas('user');

        if (filled($this->partner_account_type)) {
            $query->where('account_type', $this->partner_account_type);
        }

        return $table
            ->query($query)
            ->columns(PartnerDetailsTableSchema::columns())
            ->filters(PartnerDetailsTableSchema::filters())
            ->selectable()
            ->defaultSort('contact_person');
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('recipient_type')
                ->label('Címzett típusa')
                ->options([
                    'customer' => 'Vevők',
                    'partner' => 'Partnerek',
                ])
                ->required()
                ->native(false)
                ->live(),

            Select::make('partner_account_type')
                ->label('Partner típus szűrő')
                ->options(AccountType::options())
                ->native(false)
                ->visible(fn (Get $get) => $get('recipient_type') === 'partner')
                ->live(),

            Select::make('email_field')
                ->label('Email mező')
                ->options(fn (Get $get) => $get('recipient_type') === 'partner'
                    ? [
                        'user_email' => 'Felhasználó email (user.email)',
                        'customer_email' => 'Vevő email (customer.contact_email)',
                    ]
                    : [
                        'contact_email' => 'Kapcsolattartó email (contact_email)',
                    ]
                )
                ->default('contact_email')
                ->native(false)
                ->visible(fn (Get $get) => $get('recipient_type') === 'partner'),

            Toggle::make('test_mode')
                ->label('Teszt küldés'),

            TextInput::make('test_email')
                ->label('Teszt e-mail cím')
                ->email()
                ->visible(fn (Get $get) => $get('test_mode'))
                ->required(fn (Get $get) => $get('test_mode')),

            TextInput::make('subject')
                ->label('E-mail tárgy')
                ->required(),

            RichEditor::make('body')
                ->label('E-mail szöveg')
                ->required(),
        ];
    }

    public function send(): void
    {
        if (blank($this->subject) || blank($this->body)) {
            Notification::make()->title('Tárgy és szöveg megadása kötelező.')->danger()->send();
            return;
        }

        if ($this->test_mode && blank($this->test_email)) {
            Notification::make()->title('Teszt módban add meg a teszt email címet.')->danger()->send();
            return;
        }

        $selectedIds = $this->selectedTableRecords ?? [];
        $sentCount = 0;
        $errorCount = 0;

        if ($this->recipient_type === 'customer') {
            $records = count($selectedIds)
                ? Customer::whereIn('id', $selectedIds)->get()
                : $this->getFilteredTableQuery()->get();

            foreach ($records as $customer) {
                $email = $customer->contact_email;
                $name = $customer->contact_name ?? $customer->billing_name ?? 'Ügyfelünk';

                if (blank($email) && ! $this->test_mode) {
                    continue;
                }

                try {
                    Mail::to($this->test_mode ? $this->test_email : $email)
                        ->send(new BulkMessage($name, html_entity_decode($this->body), $this->subject));
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error('Bulk email hiba (customer)', ['id' => $customer->id, 'error' => $e->getMessage()]);
                    $errorCount++;
                }

                usleep(250000);
            }
        } else {
            $records = count($selectedIds)
                ? PartnerDetails::with(['user', 'customer'])->whereIn('id', $selectedIds)->get()
                : $this->getFilteredTableQuery()->with(['user', 'customer'])->get();

            foreach ($records as $partner) {
                $email = $this->resolvePartnerEmail($partner);
                $name = $partner->contact_person ?? $partner->user?->name ?? 'Partnerünk';

                if (blank($email) && ! $this->test_mode) {
                    continue;
                }

                try {
                    Mail::to($this->test_mode ? $this->test_email : $email)
                        ->send(new BulkMessage($name, html_entity_decode($this->body), $this->subject));
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error('Bulk email hiba (partner)', ['id' => $partner->id, 'error' => $e->getMessage()]);
                    $errorCount++;
                }

                usleep(250000);
            }
        }

        $message = "{$sentCount} e-mail sikeresen elküldve.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} hiba történt.";
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    protected function resolvePartnerEmail(PartnerDetails $partner): ?string
    {
        return match ($this->email_field) {
            'customer_email' => $partner->customer?->contact_email,
            default => $partner->user?->email,
        };
    }
}
