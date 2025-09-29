<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Models\ProductOrder;
use App\Filament\Resources\ProductOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Filament\Pages\BaseCreateRecord;
class CreateProductOrder extends BaseCreateRecord
{
    protected static string $resource = ProductOrderResource::class;
    protected function afterCreate(): void
    {
        $this->record->updateTotals(); // <- itt már biztosan léteznek az itemek

        $data = $this->form->getState();

        if (!empty($data['email_to']) && !empty($data['email_body'])) {
            try {
                Mail::html($data['email_body'], function ($message) use ($data) {
                    $message->to($data['email_to'])
                        ->subject('Új rendelés érkezett');
                });

                $this->record->update(['is_sent' => true]);

            } catch (\Throwable $e) {
                logger()->error('Rendelés e-mail küldési hiba: ' . $e->getMessage());
            }
        }
    }
    protected function getFormActions(): array
    {
        return [
        ];
    }
    public function getHeading(): string
    {
        return 'Új Termék Rendelés';
    }
    public function getBreadcrumb(): string
    {
        return 'Új Termék Rendelés';
    }
}
