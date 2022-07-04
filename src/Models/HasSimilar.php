<?php

namespace SiteOrigin\ScoutLSH\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\EngineManager;
use SiteOrigin\ScoutLSH\Facades\LSHSearcher;

trait HasSimilar
{
    public function scopeSimilar(Builder $query, ?array $weights = null): Builder
    {
        if(is_null($weights)) {
            $weights = match(True) {
                method_exists($this, 'getSimilarWeights') => $this->getSimilarWeights(),
                method_exists($this, 'getSearchWeights') => $this->getSearchWeights(),
                default => [],
            };
        }

        return LSHSearcher::searchByModel($this, $query, $weights);
    }

    public static function scopeSimilarTo(Builder $query, $model, array $weights = [])
    {
        return LSHSearcher::searchByModel($model, $query, $weights);
    }
}
