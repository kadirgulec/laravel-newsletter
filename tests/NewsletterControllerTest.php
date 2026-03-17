<?php

namespace KadirGulec\Newsletter\Tests;

use KadirGulec\Newsletter\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use KadirGulec\Newsletter\Mail\NewsletterMail;

class NewsletterControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_subscribe_a_new_user()
    {
        Mail::fake();

        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'test@example.com',
        ]);

        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function it_can_unsubscribe_a_user_with_a_signed_url()
    {
        $subscriber = Subscriber::create([
            'email' => 'unsubscribe@example.com',
            'subscribed_at' => now(),
        ]);

        $unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $response = $this->get($unsubscribeUrl);

        $response->assertStatus(200);
        $response->assertViewIs('newsletter::unsubscribe-success');

        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
    }

    /** @test */
    public function it_prevents_unsubscribing_without_a_valid_signature()
    {
        $subscriber = Subscriber::create([
            'email' => 'unsubscribe@example.com',
            'subscribed_at' => now(),
        ]);

        $invalidUrl = route('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $response = $this->get($invalidUrl);

        $response->assertStatus(403);
        $this->assertNull($subscriber->fresh()->unsubscribed_at);
    }
}
