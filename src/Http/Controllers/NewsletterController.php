<?php

namespace KadirGulec\Newsletter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KadirGulec\Newsletter\Models\Subscriber;
use KadirGulec\Newsletter\Mail\NewsletterMail;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);

        $subscriber = Subscriber::create([
            'email' => $request->email
        ]);

         Mail::to($subscriber->email)->send(new NewsletterMail($subscriber, 'Welcome!', 'Thanks for subscribing.'));

        return back()->with('success', 'You have been subscribed successfully!');
    }

    // 2. Unsubscribe (Signed Route)
    public function unsubscribe(Request $request, Subscriber $subscriber)
    {
        // Verify the signature
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        $subscriber->update([
            'is_subscribed' => false,
            'unsubscribed_at' => now(),
        ]);

        return view('newsletter::unsubscribe-success');
    }
}