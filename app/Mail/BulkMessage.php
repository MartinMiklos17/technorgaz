<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $emailBody,
        public string $emailSubject,
    ) {}

    public function build(): static
    {
        return $this
            ->subject($this->emailSubject)
            ->view('emails.bulk_message');
    }
}
