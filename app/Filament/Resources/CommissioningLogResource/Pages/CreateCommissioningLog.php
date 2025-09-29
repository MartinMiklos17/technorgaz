<?php

namespace App\Filament\Resources\CommissioningLogResource\Pages;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CommissioningLogResource;
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

            // Könyvtár biztosítása
            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }

            // Nézet render
            $pdf = Pdf::loadView('pdf.commissioning_log', ['log' => $log]);

            // Írás és ellenőrzés
            $ok = $disk->put($relative, $pdf->output());
            if (! $ok) {
                throw new \RuntimeException("Storage put() false: {$relative}");
            }

            // Modell frissítés
            $log->pdf_path = $relative;
            $log->save();

            // (dev) visszajelzés
            Notification::make()
                ->title('PDF generálva')
                ->body("Mentve: {$relative}")
                ->success()
                ->send();

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
