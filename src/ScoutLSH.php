<?php

namespace SiteOrigin\ScoutLSH;

use ArrayIterator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;

class ScoutLSH extends Engine
{
    public function update($models)
    {
        // Get all the texts that we're going to index.
        $texts = $models->map(fn($model) => $model->toSearchableArray());
        $encoded = TextEncoder::encode($texts->flatten(1)->toArray());

        // We need an iterator for $encoded
        $encoded = new ArrayIterator($encoded);

        // We need to update the $texts array to include the encoded text.
        // Encoded has been flattened though
        $texts = $texts->map(function($texts) use ($encoded) {
            foreach($texts as $key => $text) {
                $texts[$key] = $encoded->current();
                $encoded->next();
            }

            return $texts;
        });

        // Finally, we'll insert these into the index table
        $models->zip($texts)
            ->each(function($item) {
                [$model, $texts] = $item;

                foreach($texts as $key => $encoded) {
                    DB::table('lsh-search-index')
                        ->updateOrInsert(
                            [
                                'model_id' => $model->getKey(),
                                'model_type' => get_class($model),
                                'field' => $key,
                            ],
                            collect($encoded)
                                ->mapWithKeys(fn($value, $key) => ['bit_' . $key => $value])
                                ->toArray()
                        );
                }
            });
    }

    public function delete($models)
    {
        $models->each(function($model){
            DB::table('lsh-search-index')
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

    private function buildSearchQuery(Builder $builder)
    {
        $encoded = TextEncoder::encode([$builder->query])[0];

        $similarity = collect($encoded)
            ->map(fn ($v, $i) => 'BIT_COUNT(`bit_' . (int) $i . '` ^ ' . (int) $v . ')')
            ->join('+');
        $similarity = '0.5 - (' . $similarity . ') / 512';

        $model = $builder->model;
        $weights = method_exists($model, 'getTypeWeights') ? $model->getTypeWeights() : [];

        if(!empty($weights)) {
            $weighting = 'CASE `field`';
            foreach($weights as $type => $weight) {
                $weighting .= ' WHEN \'' . addslashes($type) . '\' THEN ' . (float) $weight;
            }
            $weighting .= ' END';
            $similarity .= ' * (' . $weighting . ')';
        }

        $sub = DB::table('lsh-search-index')
            ->selectRaw('`model_type`, `model_id`, `field`, ' . $similarity . ' AS `similarity`')
            ->where('model_type', get_class($model));

        // Select from this subquery where we group by model_id and make score the sum of all the similarities
        $sub2 = DB::table('lsh-search-index')
            ->fromSub($sub, 'sub')
            ->groupBy('model_id')
            ->selectRaw('`model_id`, SUM(`similarity`) AS `score`')
            ->orderByDesc('score');

        // Now, we need to create a $model query that returns models that match the search query.
        // We'll do this by joining the results table to the model table.
        return $model->query()
            ->leftJoinSub($sub2, 'sub2', 'sub2.model_id', '=', $model->getTable() . '.id')
            ->orderByDesc('score');
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
