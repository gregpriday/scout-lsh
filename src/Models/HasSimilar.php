<?php

namespace SiteOrigin\ScoutLSH\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasSimilar
{
    public function findSimilar(?Builder $filter = null): Builder
    {
        $thisIndex = DB::table('lsh-search-index')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->getKey());

        $similarityQuery = DB::table('lsh-search-index', 'lsh1')
            ->where('lsh1.model_id', '!=', $this->getKey())
            ->leftJoinSub($thisIndex, 'lsh2', function ($join) {
                $join->on('lsh1.model_type', '=', 'lsh2.model_type');
                $join->on('lsh1.field', '=', 'lsh2.field');
            });

        $similarity = [];
        for ($i = 0; $i < 96; $i++) {
            $similarity[] = 'BIT_COUNT(`lsh1`.`bit_' . $i . '` ^ `lsh2`.`bit_' . $i . '`)';
        }
        $similarity = '0.5 - (' . implode(' + ', $similarity) . ') / 512';

        // If $this implements FieldWeights interface
        if (is_a($this, FieldWeights::class)) {
            $weights = $this->getTypeWeights();
            $weighting = 'CASE `lsh1`.`field`';
            foreach ($weights as $type => $weight) {
                $weighting .= ' WHEN \'' . addslashes($type) . '\' THEN ' . (float) $weight;
            }
            $weighting .= ' END';
            $similarity .= ' * (' . $weighting . ')';

            // Only look at fields that have a set weight
            $similarityQuery->whereIn('lsh1.field', array_keys($weights));
        }

        $similarityQuery
            ->selectRaw('`lsh1`.`model_id`, ' . $similarity . ' AS `similarity`');

        if (! is_null($filter)) {
            $filter = $filter->getQuery()->select($this->getKeyName());
            $similarityQuery->whereIn('lsh1.model_id', $filter);
        }

        $combinedQuery = DB::table('lsh-search-index')
            ->fromSub($similarityQuery, 'similarity_query')
            ->groupBy('similarity_query.model_id')
            ->selectRaw('`similarity_query`.`model_id`, SUM(`similarity`) AS `score`')
            ->orderByDesc('score');

        return $this->newQuery()
            ->rightJoinSub($combinedQuery, 'combined', $this->getTable() . '.' . $this->getKeyName(), '=', 'combined.model_id')
            ->orderByDesc('combined.score');
    }
}
