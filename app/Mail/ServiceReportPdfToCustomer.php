<?php

namespace App\Mail;

use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ServiceReportPdfToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public ServiceReport $report;
    public string $reportTypeLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(ServiceReport $report)
    {
        $this->report = $report;

        $typeLabels = [
            'maintenance_warranty'                 => 'Karbantartás (garanciális)',
            'maintenance_non_warranty'             => 'Karbantartás (garancián kívüli)',
            'repair_warranty'                      => 'Javítás (garanciális)',
            'repair_non_warranty'                  => 'Javítás (garancián kívüli)',
            'maintenance_not_covered_by_warranty'  => 'Garanciába nem vehető készülék karbantartás',
            'repair_not_covered_by_warranty'       => 'Garanciába nem vehető készülék javítás',
        ];

        $this->reportTypeLabel = $typeLabels[$report->report_type] ?? $report->report_type ?? 'Szerviznapló';
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $subject = 'Szerviznapló - ' . $this->reportTypeLabel;

        if (! empty($this->report->serial_number)) {
            $subject .= ' - ' . $this->report->serial_number;
        }

        $mail = $this
            ->subject($subject)
            ->view('emails.service_report_to_customer')
            ->with([
                'report'          => $this->report,
                'reportTypeLabel' => $this->reportTypeLabel,
            ]);

        // PDF csatolása a private diszkről, ha van
        if (! empty($this->report->pdf_path)) {
            $disk = Storage::disk('private');
            $relativePath = $this->report->pdf_path;

            if ($disk->exists($relativePath)) {
                $fullPath = $disk->path($relativePath);

                $mail->attach($fullPath, [
                    'as'   => basename($fullPath),
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
