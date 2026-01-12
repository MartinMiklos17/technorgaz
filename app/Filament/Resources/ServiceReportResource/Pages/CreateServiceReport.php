<?php

namespace App\Filament\Resources\ServiceReportResource\Pages;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Filament\Resources\ServiceReportResource;
use App\Mail\ServiceReportPdfToCustomer;
use App\Models\CommissioningLog;
use App\Models\Product;

class CreateServiceReport extends CreateRecord
{
    protected static string $resource = ServiceReportResource::class;

    public function getHeading(): string
    {
        return 'Új Szervíznapló';
    }

    public function getBreadcrumb(): string
    {
        return 'Új Szervíznapló';
    }

    protected function afterCreate(): void
    {
        $report = $this->record;
        $disk   = Storage::disk('private');

        try {
            // 1) FOTÓK MOZGATÁSA (ugyanaz, csak return nélkül!)
            $paths = (array) $report->photo_paths;

            if (! empty($paths)) {
                $targetDir = "service_reports/img/{$report->id}";
                if (! $disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                $new = [];
                foreach ($paths as $p) {
                    $src = str_replace('\\', '/', $p);
                    $filename  = basename($src);
                    $dest = "{$targetDir}/{$filename}";

                    if ($src !== $dest && $disk->exists($src)) {
                        $disk->move($src, $dest);
                    }
                    $new[] = $dest;
                }

                $report->photo_paths = $new;
                $report->save();
            }

            // 2) PDF GENERÁLÁS
            $dir      = "service_reports/pdf/{$report->id}";
            $fileName = sprintf('%06d-%s.pdf', $report->id, Str::slug($report->serial_number ?? 'no-serial'));
            $relative = $dir . '/' . $fileName;

            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }

            $pdf = Pdf::loadView('pdf.service_report', ['report' => $report]);

            $ok = $disk->put($relative, $pdf->output());
            if (! $ok) {
                throw new \RuntimeException("Storage put() false: {$relative}");
            }

            $report->pdf_path = $relative;
            $report->save();

            // 3) E-MAIL KÜLDÉS ÜGYFÉLNEK (ha van email)
            if (filled($report->customer_email)) {
                try {
                    Mail::to($report->customer_email)
                        ->send(new ServiceReportPdfToCustomer($report));

                    Notification::make()
                        ->title('Szerviznapló PDF generálva és elküldve az ügyfélnek')
                        ->body("Fájl: {$relative} • Címzett: {$report->customer_email}")
                        ->success()
                        ->send();
                } catch (Throwable $mailEx) {
                    Log::error('ServiceReport email küldési hiba', [
                        'record_id' => $report->id,
                        'email'     => $report->customer_email,
                        'msg'       => $mailEx->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Szerviznapló e-mail küldés sikertelen')
                        ->body($mailEx->getMessage())
                        ->danger()
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('Szerviznapló PDF generálva')
                    ->body("Nincs ügyfél e-mail cím megadva. Mentve: {$relative}")
                    ->warning()
                    ->send();
            }

        } catch (Throwable $e) {
            Log::error('ServiceReport PDF mentési/generálási hiba', [
                'record_id' => $report->id ?? null,
                'msg'       => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Szerviznapló PDF generálás/mentés sikertelen')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['commissioning_log_id'])) {
            $log = CommissioningLog::find($data['commissioning_log_id']);
            if ($log) {
                $data['serial_number']           = $log->serial_number;
                $data['product_id']              = $log->product_id;
                $data['customer_name']           = $log->customer_name;
                $data['customer_email']          = $log->customer_email;
                $data['customer_phone']          = $log->customer_phone;
                $data['customer_zip']            = $log->customer_zip;
                $data['customer_city']           = $log->customer_city;
                $data['customer_street']         = $log->customer_street;
                $data['customer_street_number']  = $log->customer_street_number;
                $data['created_at']              = $data['created_at'] ?? now();
            }
        }

        if (empty($data['serial_number'])) {
            throw ValidationException::withMessages([
                'commissioning_log_id' => 'Válassz beüzemelési naplót (a gyári szám kötelező).',
            ]);
        }

        $data['created_by'] = auth()->id();

        // SID generálása a régi logika alapján
        $data['sid'] = $this->generateSid($data);

        return $data;
    }

    // (opcionális) Edit oldalra ugyanez:
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['commissioning_log_id'])) {
            $log = CommissioningLog::find($data['commissioning_log_id']);
            if ($log) {
                $data['serial_number'] = $log->serial_number;
                $data['product_id']    = $log->product_id;
            }
        }
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
}
