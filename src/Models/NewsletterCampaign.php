<?php

namespace KadirGulec\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use KadirGulec\Newsletter\Mail\NewsletterMail;

class NewsletterCampaign extends Model
{

    protected $fillable = [
            'subject',
            'content',
            'status',
            'sent_at'
    ];

    protected $casts = [
            'sent_at' => 'datetime'
    ];

    public function send()
    {
        $subscribers = Subscriber::active()->get();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                    ->queue(new NewsletterMail(
                                    $subscriber,
                                    $this->subject,
                                    $this->content
                            )
                    );
        }
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

    }
}