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

        $subscriber = Subscriber::firstWhere('email', 'test@example.com');
        $this->assertNotNull($subscriber->subscribed_at, 'subscribed_at must be set so new subscribers are active');
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertTrue(Subscriber::active()->where('email', 'test@example.com')->exists());

        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function it_can_resubscribe_a_user_who_previously_unsubscribed()
    {
        Mail::fake();

        Subscriber::create([
            'email' => 'returning@example.com',
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => now()->subWeek(),
        ]);

        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'returning@example.com',
        ]);

        $response->assertSessionHas('success');

        $subscriber = Subscriber::firstWhere('email', 'returning@example.com');
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertTrue($subscriber->subscribed_at->isToday());
        $this->assertSame(1, Subscriber::where('email', 'returning@example.com')->count());

        Mail::assertSent(NewsletterMail::class);
    }

    /** @test */
    public function it_is_idempotent_for_already_active_subscribers()
    {
        Mail::fake();

        Subscriber::create([
            'email' => 'already@example.com',
            'subscribed_at' => now()->subDay(),
        ]);

        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'already@example.com',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(1, Subscriber::where('email', 'already@example.com')->count());

        Mail::assertNothingSent();
    }

    /** @test */
    public function get_on_signed_unsubscribe_url_shows_confirmation_but_does_not_unsubscribe()
    {
        // Protects against link-prefetchers and antivirus scanners that
        // would otherwise follow the unsubscribe URL automatically.
        $subscriber = Subscriber::create([
            'email' => 'prefetch@example.com',
            'subscribed_at' => now(),
        ]);

        $url = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('newsletter::confirm-unsubscribe');
        $this->assertNull($subscriber->fresh()->unsubscribed_at);
    }

    /** @test */
    public function post_on_signed_unsubscribe_url_unsubscribes_the_user()
    {
        $subscriber = Subscriber::create([
            'email' => 'unsubscribe@example.com',
            'subscribed_at' => now(),
        ]);

        $url = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $response = $this->post($url);

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

        $this->get($invalidUrl)->assertStatus(403);
        $this->post($invalidUrl)->assertStatus(403);

        $this->assertNull($subscriber->fresh()->unsubscribed_at);
    }
}
