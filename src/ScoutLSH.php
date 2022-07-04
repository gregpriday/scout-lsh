<?php

namespace SiteOrigin\ScoutLSH;

use ArrayIterator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Contracts\PaginatesEloquentModels;
use Laravel\Scout\Engines\Engine;
use SiteOrigin\ScoutLSH\Facades\LSHSearcher;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Models\FieldWeights;

class ScoutLSH extends Engine implements PaginatesEloquentModels
{
    public function update($models)
    {
        // Get all the texts that we're going to index.
        $texts = $models->map(fn ($model) => $model->toSearchableArray());
        $encoded = TextEncoder::encode($texts->flatten(1)->map(fn ($t) => strtolower($t))->toArray());

        // We need an iterator for $encoded
        $encoded = new ArrayIterator($encoded);

        // We need to update the $texts array to include the encoded text.
        // Encoded has been flattened though
        $texts = $texts->map(function ($texts) use ($encoded) {
            foreach ($texts as $key => $text) {
                $texts[$key] = $encoded->current();
                $encoded->next();
            }

            return $texts;
        });

        // Finally, we'll insert these into the index table
        $models->zip($texts)
            ->each(function ($item) {
                [$model, $texts] = $item;

                foreach ($texts as $key => $encoded) {
                    DB::table('lsh_search_index')
                        ->updateOrInsert(
                            [
                                'model_id' => $model->getKey(),
                                'model_type' => get_class($model),
                                'field' => $key,
                            ],
                            collect(str_split($encoded))
                                ->chunk(16)
                                ->map(fn ($chunk) => base_convert($chunk->join(''), 16, 10))
                                ->mapWithKeys(fn ($char, $i) => ["bit_{$i}" => $char])
                                ->toArray()
                        );
                }
            });
    }

    public function delete($models)
    {
        $models->each(function ($model) {
            DB::table('lsh_search_index')
                ->where('model_id', $model->getKey())
                ->where('model_type', get_class($model))
                ->delete();
        });
    }

    public function search(Builder $builder)
    {
        $models = $this->buildSearchQuery($builder)->get();

        return [
            'results' => $models,
            'total' => $models->count(),
        ];
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->buildSearchQuery($builder)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function simplePaginate(Builder $builder, $perPage, $page)
    {
        return $this->buildSearchQuery($builder)
            ->simplePaginate($perPage, ['*'], 'page', $page);
    }

    public function buildSearchQuery(Builder $builder)
    {
        // Get the search query
        $query = $builder->query;
        $query = Cache::remember(
            config('lsh.query.cache_key') . "[{$query}]",
            now()->addMinutes(config('lsh.query.cache_duration')),
            fn() => TextEncoder::encode(['query: ' . strtolower($query)])[0]
        );
        $model = $builder->model;

        // Check if we're searching inside another query
        if (! empty($builder->index) && $builder->index instanceof \Illuminate\Database\Eloquent\Builder) {
            $indexQuery = $builder->index;
        } else {
            $indexQuery = $model::query();
        }

        $weights = method_exists($model, 'getSearchWeights') ? $model->getSearchWeights() : [];
        return LSHSearcher::searchByEncoded($query, $indexQuery, config('lsh.search_candidates'), $weights);
    }

    public function mapIds($results)
    {
        $results = $results['results'];

        return count($results) > 0
            ? $results->modelKeys()
            : collect();
    }

    public function map(Builder $builder, $results, $model)
    {
        return $results['results'];
    }

    public function lazyMap(Builder $builder, $results, $model)
    {
        return new LazyCollection($results['results']->all());
    }

    public function getTotalCount($results)
    {
        return $results['total'];
    }

    public function flush($model)
    {
    }

    public function createIndex($name, array $options = [])
    {
    }

    public function deleteIndex($name)
    {
    }
}
