<?php

namespace SiteOrigin\ScoutLSH;

use Illuminate\Support\Facades\Blade;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use SiteOrigin\ScoutLSH\Services\AutoLinker;
use SiteOrigin\ScoutLSH\Services\LSHSearcher;
use SiteOrigin\ScoutLSH\Services\TextEncoder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScoutLSHServiceProvider extends PackageServiceProvider
{
    public function packageBooted()
    {
        app(EngineManager::class)->extend('vector', function () {
            return new ScoutLSH();
        });

        Builder::macro('withFieldWeights', function (array $weights) {
            $this->fieldWeights = $weights;

            return $this;
        });

        $this->app->singleton(TextEncoder::class, function () {
            return new TextEncoder(config('lsh.encoder'));
        });

        $this->app->singleton(LSHSearcher::class, function () {
            return new LSHSearcher(app(TextEncoder::class));
        });

        $this->app->singleton(AutoLinker::class, function () {
            return new AutoLinker(app(LSHSearcher::class), app(TextEncoder::class));
        });

        //Blade::directive('autolink', function($expression){
        //
        //});
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scout-lsh')
            ->hasConfigFile('lsh')
            ->hasMigration('create_lash_search_index_table.php');
    }
}
