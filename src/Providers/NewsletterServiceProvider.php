<?php

namespace MyVendor\Newsletter\Providers;

use Illuminate\Support\ServiceProvider;

class NewsletterServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Load Views (Namespace: newsletter::)
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'newsletter');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');

        // Publishable assets (optional)
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/newsletter'),
        ], 'newsletter-views');
    }
}