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
use App\Models\Product;
use Livewire\Attributes\On;

class CreateCommissioningLog extends CreateRecord
{
    protected static string $resource = CommissioningLogResource::class;

    #[On('serialNumberScanned')]
    public function onSerialNumberScanned($value)
    {
        $this->data['serial_number'] = $value;
    }

    public function getHeading(): string
    {
        return 'Új Beüzemelési Napló';
    }

    public function getBreadcrumb(): string
    {
        return 'Új Beüzemelési Napló';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_at'] = $data['created_at'] ?? now();
        $data['created_by'] = auth()->id();

        // SID generálása a régi logika alapján
        $data['sid'] = $this->generateSid($data);

        return $data;
    }

    /**
     * Régi rendszerrel kompatibilis SID generálás.
     */
    private function generateSid(array $data): string
    {
        $productName = optional(Product::find($data['product_id'] ?? null))->name ?? 'unknown';

        $userId = auth()->id() ?? 0;

        $burner = str_replace('.', '', (string)($data['burner_pressure'] ?? ''));
        $water  = str_replace('.', '', (string)($data['water_pressure'] ?? ''));
        $co2    = str_replace('.', '', (string)($data['co2_value'] ?? ''));
        $co     = str_replace('.', '', (string)($data['co_value'] ?? ''));

        $raw = $productName
            . $userId
            . $burner
            . $water
            . $co2
            . $co
            . now()->format('YmdHis');

        return $this->cleanString($raw);
    }

    private function cleanString(string $value): string
    {
        $value = trim($value);
        $value = str_replace(' ', '-', $value);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $value);
    }

    protected function afterCreate(): void
    {
        $log = $this->record;

        // ezt már a mutateFormDataBeforeCreate-ben beállítottuk, de nem baj, ha itt is:
        $log->created_by = auth()->id();

        try {
            // … a meglévő PDF + e-mail logika változatlan …
            $disk = Storage::disk('private');
            $paths = (array) ($log->photo_paths ?? []);
            if (! empty($paths)) {
                $targetDir = "commissioning_logs/img/{$log->id}";
                if (! $disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                $new = [];
                foreach ($paths as $p) {
                    $src = str_replace('\\', '/', $p);
                    $filename = basename($src);
                    $dest = "{$targetDir}/{$filename}";

                    if ($src !== $dest && $disk->exists($src)) {
                        $disk->move($src, $dest);
                    }
                    $new[] = $dest;
                }

                $log->photo_paths = $new;
                $log->save();
            }

            $dir      = "commissioning_logs/pdf/{$log->id}";
            $fileName = sprintf('%06d-%s.pdf', $log->id, Str::slug($log->serial_number ?? 'no-serial'));
            $relative = $dir . '/' . $fileName;

            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }

            $pdf = Pdf::loadView('pdf.commissioning_log', ['log' => $log]);

            $ok = $disk->put($relative, $pdf->output());
            if (! $ok) {
                throw new \RuntimeException("Storage put() false: {$relative}");
            }

            $log->pdf_path = $relative;
            $log->save();

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
