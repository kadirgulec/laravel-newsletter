<?php

namespace KadirGulec\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $email
 * @property string $subscribed_at
 * @property string $unsubscribed_at
 * @property string $unsubscribe_reason
 */
class Subscriber extends Model
{
    use Notifiable;

    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNotNull('subscribed_at')->whereNull('unsubscribed_at');
    }
}