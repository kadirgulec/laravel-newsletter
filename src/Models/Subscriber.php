<?php

namespace KadirGulec\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Subscriber extends Model
{
    use Notifiable;

    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'is_subscribed',
        'unsubscribed_at'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_subscribed', true);
    }
}