<?php

namespace App\Mail;

use MailchimpTransactional\ApiClient;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Illuminate\Support\Facades\Log;

class MailchimpTransport extends AbstractTransport
{
    protected $client;

    public function __construct()
    {
        // Initialize the EventDispatcher
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MessageLoggerListener());

        // Set the dispatcher in the AbstractTransport class
        parent::__construct($dispatcher);

        // Initialize Mailchimp API client
        $this->client = new ApiClient();
        $this->client->setApiKey(env('MAILCHIMP_API_KEY'));
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        $from = $email->getFrom()[0]->getAddress();
        $subject = $email->getSubject();
        $htmlContent = $email->getHtmlBody();
        $to = $email->getTo()[0]->getAddress();

        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'type' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
                'name' => $attachment->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename'),
                'content' => base64_encode($attachment->getBody()), // csak simán
            ];
        }
        $mailchimpMessage = [
            "from_email" => $from,
            "subject" => $subject,
            "html" => $htmlContent,
            "to" => [
                [
                    "email" => $to,
                    "type" => "to"
                ]
                ],
            "attachments" => $attachments,
        ];

        try {
            $response = $this->client->messages->send(["message" => $mailchimpMessage]);
            $responseStatus = $response[0]->status;

            // Log the response for debugging
            Log::info('Mailchimp API Response:', ['response' => $response]);

            if ($responseStatus !== "sent") {
                Log::error('Mailchimp API Error:', ['response' => $response]);
                throw new \Exception("Mailchimp API returned an error: " . json_encode($response));
            }

        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Mailchimp API Exception:', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    public function __toString(): string
    {
        return 'mailchimp';
    }
}
