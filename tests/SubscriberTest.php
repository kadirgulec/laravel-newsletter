<?php

namespace KadirGulec\Newsletter\Tests;

use KadirGulec\Newsletter\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_determine_active_subscribers()
    {
        Subscriber::create([
            'email' => 'active@example.com',
            'subscribed_at' => now(),
        ]);

        Subscriber::create([
            'email' => 'unsubscribed@example.com',
            'subscribed_at' => now(),
            'unsubscribed_at' => now(),
        ]);

        Subscriber::create([
            'email' => 'never_subscribed@example.com',
            'subscribed_at' => null,
        ]);

        $activeSubscribers = Subscriber::active()->get();

        $this->assertCount(1, $activeSubscribers);
        $this->assertEquals('active@example.com', $activeSubscribers->first()->email);
    }
}
