<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CommissioningLogResource;
use App\Mail\CommissioningLogPdfToCustomer;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class CreateCommissioningLog extends CreateRecord
{
    protected static string $resource = CommissioningLogResource::class;

    protected function afterCreate(): void
    {
        $log = $this->record;

        try {
            $disk = Storage::disk('private');

            $dir      = 'commissioning_logs';
            $fileName = sprintf('%06d-%s.pdf', $log->id, Str::slug($log->serial_number ?? 'no-serial'));
            $relative = $dir . '/' . $fileName;

            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }

            // PDF render
            $pdf = Pdf::loadView('pdf.commissioning_log', ['log' => $log]);

            // Mentés és ellenőrzés
            $ok = $disk->put($relative, $pdf->output());
            if (! $ok) {
                throw new \RuntimeException("Storage put() false: {$relative}");
            }

            // Model frissítés
            $log->pdf_path = $relative;
            $log->save();

            // Ügyfél e-mail (ha van cím)
            if (filled($log->customer_email)) {
                try {
                    Mail::to($log->customer_email)
                        ->send(new CommissioningLogPdfToCustomer($log));

                    Notification::make()
                        ->title('PDF generálva és elküldve az ügyfélnek')
                        ->body("Fájl: {$relative} • Címzett: {$log->customer_email}")
                        ->success()
                        ->send();
                } catch (Throwable $mailEx) {
                    Log::error('CommissioningLog email küldési hiba', [
                        'record_id' => $log->id,
                        'email'     => $log->customer_email,
                        'msg'       => $mailEx->getMessage(),
                    ]);

                    Notification::make()
                        ->title('E-mail küldés sikertelen')
                        ->body($mailEx->getMessage())
                        ->danger()
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('PDF generálva')
                    ->body("Nincs ügyfél e-mail cím megadva. Mentve: {$relative}")
                    ->warning()
                    ->send();
            }

        } catch (Throwable $e) {
            Log::error('CommissioningLog PDF mentési hiba', [
                'record_id' => $log->id ?? null,
                'msg'       => $e->getMessage(),
            ]);

            Notification::make()
                ->title('PDF generálás/mentés sikertelen')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
