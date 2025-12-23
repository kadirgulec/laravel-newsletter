<?php

namespace KadirGulec\Newsletter\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use KadirGulec\Newsletter\Models\Subscriber;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriber;
    public $content;
    public $subject;
    public $unsubscribeUrl;

    public function __construct(Subscriber $subscriber, string $subject, string $content)
    {
        $this->subscriber = $subscriber;
        $this->subject = $subject;
        $this->content = $content;

        $this->unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'newsletter::email.standard',
        );
    }

    /**
     * Add the List-Unsubscribe header here.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . $this->unsubscribeUrl . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'
            ],
        );
    }
}