<?php

namespace KadirGulec\Newsletter\Tests;

use KadirGulec\Newsletter\Models\NewsletterCampaign;
use KadirGulec\Newsletter\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use KadirGulec\Newsletter\Mail\NewsletterMail;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_a_campaign_to_active_subscribers()
    {
        Mail::fake();

        Subscriber::create([
            'email' => 'active@example.com',
            'subscribed_at' => now(),
        ]);

        Subscriber::create([
            'email' => 'unsubscribed@example.com',
            'subscribed_at' => now(),
            'unsubscribed_at' => now(),
        ]);

        $campaign = NewsletterCampaign::create([
            'subject' => 'Monthly News',
            'content' => '<p>Hello world</p>',
            'status' => 'draft',
        ]);

        $campaign->send();

        Mail::assertQueued(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('active@example.com') &&
                   $mail->subject === 'Monthly News';
        });

        Mail::assertNotQueued(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('unsubscribed@example.com');
        });

        $this->assertEquals('sent', $campaign->fresh()->status);
        $this->assertNotNull($campaign->fresh()->sent_at);
    }
}
