<?php

namespace SiteOrigin\ScoutLSH;

use Laravel\Scout\EngineManager;
use SiteOrigin\ScoutLSH\Services\TextEncoder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SiteOrigin\ScoutLSH\Commands\ScoutLSHCommand;

class ScoutLSHServiceProvider extends PackageServiceProvider
{
    public function packageBooted()
    {
        resolve(EngineManager::class)->extend('vector', function () {
            return new ScoutLSH();
        });

        $this->app->singleton(TextEncoder::class, function(){
            return new TextEncoder(config('lsh.encoder'));
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scout-lsh')
            ->hasConfigFile('lsh')
            ->hasMigration('create_lash_search_index_table.php')
            ->hasCommand(ScoutLSHCommand::class);
    }
}
