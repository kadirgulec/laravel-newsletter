<?php

namespace KadirGulec\Newsletter\Tests;

use KadirGulec\Newsletter\Providers\NewsletterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            NewsletterServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }
}
