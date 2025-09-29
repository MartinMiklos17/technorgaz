<?php

namespace App\Mail;

use App\Models\CommissioningLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Filesystem\Path; // <- normalizáláshoz

class CommissioningLogPdfToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CommissioningLog $log) {}

    public function build(): self
    {
        $subject = 'Beüzemelési napló – ' . ($this->log->serial_number ?? ('#' . $this->log->id));

        $mailable = $this->subject($subject)
            ->view('emails.commissioning_log_customer')
            ->with(['log' => $this->log]);

        // BCC
        /*$bcc = (string) config('mail.technorgaz_sales');
        if ($bcc !== '') {
            $addresses = array_filter(array_map('trim', explode(',', $bcc)));
            if ($addresses) {
                $mailable->bcc($addresses);
            }
        }
*/
        $diskName = 'private';
        $path     = $this->log->pdf_path;

        if ($path && Storage::disk($diskName)->exists($path)) {
            // 1) Elsődleges: attachFromStorageDisk (nem kell abszolút út)
            try {
                $mailable->attachFromStorageDisk(
                    disk: $diskName,
                    path: $path,
                    name: 'commissioning-log-' . $this->log->id . '.pdf',
                    options: ['mime' => 'application/pdf']
                );
                return $mailable;
            } catch (\Throwable $e) {
                Log::warning('attachFromStorageDisk sikertelen, absolute path fallback', [
                    'record_id' => $this->log->id,
                    'path'      => $path,
                    'msg'       => $e->getMessage(),
                ]);
            }

            // 2) Fallback: abszolút útvonal natívan, normalizált elválasztókkal
            try {
                $root     = (string) config('filesystems.disks.' . $diskName . '.root');
                $absolute = rtrim($root, "/\\") . DIRECTORY_SEPARATOR . ltrim($path, "/\\");
                // normalizálás: minden / vagy \ -> rendszer szerinti elválasztó
                $absolute = preg_replace('#[\\\\/]+#', DIRECTORY_SEPARATOR, $absolute);
                // ha a rendszer fel tudja oldani, vedd a canonical path-ot
                $real = realpath($absolute) ?: $absolute;

                $mailable->attach(
                    $real,
                    ['as' => 'commissioning-log-' . $this->log->id . '.pdf', 'mime' => 'application/pdf']
                );
                return $mailable;
            } catch (\Throwable $e) {
                Log::warning('attach(file) sikertelen, attachData fallback', [
                    'record_id' => $this->log->id,
                    'absolute'  => isset($real) ? $real : null,
                    'msg'       => $e->getMessage(),
                ]);
            }

            // 3) Végső fallback: binárisan beolvassuk és attachData
            try {
                $binary = Storage::disk($diskName)->get($path);
                $mailable->attachData(
                    $binary,
                    'commissioning-log-' . $this->log->id . '.pdf',
                    ['mime' => 'application/pdf']
                );
                return $mailable;
            } catch (\Throwable $e) {
                Log::error('attachData sikertelen', [
                    'record_id' => $this->log->id,
                    'path'      => $path,
                    'msg'       => $e->getMessage(),
                ]);
            }
        } else {
            Log::error('PDF csatolmány nem található a private diszken', [
                'record_id' => $this->log->id,
                'path'      => $path,
            ]);
        }

        // Ha idáig jutunk, melléklet nélkül megy (a logban ott lesz az ok)
        return $mailable;
    }
}
