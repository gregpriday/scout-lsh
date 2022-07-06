<?php

namespace SiteOrigin\ScoutLSH;

use Illuminate\Support\Facades\Cache;
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
            return new AutoLinker(app(LSHSearcher::class));
        });

        Cache::macro('rememberMultiple', function (array $inputs, $ttl, callable $callback) {
            $cacheKeys = array_keys($inputs);
            $cacheValues = Cache::getMultiple($cacheKeys);

            // Missing values are null
            $missing = collect($cacheValues)
                ->filter(fn ($value) => is_null($value))
                ->mapWithKeys(fn ($value, $key) => [$key => $inputs[$key]])
                ->toArray();

            if (count($missing) > 0) {
                $new = $callback($missing);
                Cache::setMultiple($new, $ttl);

                $cacheValues = array_merge($cacheValues, $new);
            }

            return $cacheValues;
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scout-lsh')
            ->hasConfigFile('lsh')
            ->hasMigration('create_lash_search_index_table.php');
    }
}
