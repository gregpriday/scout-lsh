<?php

namespace SiteOrigin\ScoutLSH;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use SiteOrigin\ScoutLSH\Commands\ScoutLSHCommand;
use SiteOrigin\ScoutLSH\Services\TextEncoder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScoutLSHServiceProvider extends PackageServiceProvider
{
    public function packageBooted()
    {
        resolve(EngineManager::class)->extend('vector', function () {
            return new ScoutLSH();
        });

        Builder::macro('withFieldWeights', function (array $weights) {
            $this->fieldWeights = $weights;
            return $this;
        });

        $this->app->singleton(TextEncoder::class, function () {
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
