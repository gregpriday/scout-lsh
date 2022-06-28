<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use SiteOrigin\ScoutLSH\ScoutLSHServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SiteOrigin\\ScoutLSH\\Tests\\database\\factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ScoutLSHServiceProvider::class,
            ScoutServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'mysql');
        config()->set('scout.driver', 'vector');
    }

    public function defineDatabaseMigrationsAfterDatabaseRefreshed()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations/'));
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations/'));
    }
}
