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
            'email' => 'required|email',
        ]);

        $subscriber = Subscriber::firstOrNew(['email' => $request->email]);

        if ($subscriber->exists && $subscriber->subscribed_at && ! $subscriber->unsubscribed_at) {
            return back()->with('success', 'You are already subscribed.');
        }

        $subscriber->fill([
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ])->save();

        Mail::to($subscriber->email)->send(new NewsletterMail($subscriber, 'Welcome!', 'Thanks for subscribing.'));

        return back()->with('success', 'You have been subscribed successfully!');
    }

    public function showUnsubscribe(Request $request, Subscriber $subscriber)
    {
        return view('newsletter::confirm-unsubscribe', [
            'subscriber' => $subscriber,
            'actionUrl' => $request->fullUrl(),
        ]);
    }

    public function unsubscribe(Request $request, Subscriber $subscriber)
    {
        $subscriber->update([
            'unsubscribed_at' => now(),
        ]);

        return view('newsletter::unsubscribe-success');
    }
}