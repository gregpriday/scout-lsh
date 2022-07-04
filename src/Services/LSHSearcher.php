<?php

namespace SiteOrigin\ScoutLSH\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LSHSearcher
{
    /**
     * @var \SiteOrigin\ScoutLSH\Services\TextEncoder
     */
    private TextEncoder $encoder;

    public function __construct(TextEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Search the provided
     *
     * @param string $query
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $candidates
     * @param array $weights
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchByQuery(string $query, Builder $builder, int $candidates = 100, array $weights = []): Builder
    {
        $query = $this->encoder->encode([$query])[0];
        $query = collect(str_split($query))
            ->chunk(16)
            ->map(fn ($chunk) => base_convert($chunk->join(''), 16, 10));

        return $this->searchByEncoded($query, $builder, $candidates, $weights);
    }

    /**
     * @param array|\Illuminate\Database\Eloquent\Builder $query The encoded array for a search query.
     * @param \Illuminate\Database\Eloquent\Builder $builder The builder that defines what we'll be searching
     * @param int $candidates
     * @param array $weights
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchByEncoded(Collection $query, Builder $builder, int $candidates = 250, array $weights = []): Builder
    {
        // Create the query for the candidates
        $candidatesQuery = DB::table('lsh_search_index')
            ->select([
                'id',
                DB::raw($this->bitSimilarityQuery($query->take(4)) . ' * ' . $this->weightingQuery($weights) . ' AS score')
            ])
            ->where('model_type', $builder->getModel()->getMorphClass())
            ->whereIn('model_id', $builder->select('id'))
            ->orderByDesc('score')
            ->having('score', '>', 0)
            ->limit($candidates);

        if(!empty($weights)) {
            $candidatesQuery->whereIn('field', array_keys($weights));
        }

        $resultsQuery = DB::table('lsh_search_index')
            ->selectRaw('model_type, model_id, field, ' . $this->bitSimilarityQuery($query) . ' * ' . $this->weightingQuery($weights) . ' AS score')
            ->rightJoinSub($candidatesQuery, 'candidates', 'candidates.id', '=', 'lsh_search_index.id');

        return $this->combineIntoEloquentQuery($resultsQuery, $builder);
    }

    /**
     * Search for similar models to the one given.
     *
     * @param $model
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $candidates
     * @param array $weights
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchByModel($model, Builder $builder, array $weights = []): Builder
    {
        // Create the query for the candidates
        $resultsQuery = DB::table('lsh_search_index')
            ->select([
                'lsh_search_index.model_type',
                'lsh_search_index.model_id',
                'lsh_search_index.field',
                DB::raw('(' . $this->bitSimilarColumns(16, 'lsh_search_index_model', 'lsh_search_index') . ') * (' . $this->weightingQuery($weights, 'lsh_search_index') . ') AS score'),
            ])
            ->leftJoin('lsh_search_index AS lsh_search_index_model', function (QueryBuilder $join) use ($model) {
                $join
                    ->on('lsh_search_index_model.field', '=', 'lsh_search_index.field')
                    ->where('lsh_search_index_model.model_type', '=', $model->getMorphClass())
                    ->where('lsh_search_index_model.model_id', '=', (int) $model->getKey());
            })
            ->where('lsh_search_index.model_type', $builder->getModel()->getMorphClass())
            ->where('lsh_search_index.model_id', '!=', $model->getKey())
            ->whereIn('lsh_search_index.model_id', $builder->select('id'))
            ->orderByDesc('score')
            ->having('score', '>', 0);

        if(!empty($weights)) {
            $resultsQuery->whereIn('lsh_search_index.field', array_keys($weights));
        }

        return $this->combineIntoEloquentQuery($resultsQuery, $builder);
    }

    private function combineIntoEloquentQuery(QueryBuilder $results, Builder $builder): \Illuminate\Database\Eloquent\Model|Builder
    {
        $combinedQuery = DB::table('lsh_search_index')
            ->fromSub($results, 'sub')
            ->groupBy('model_id')
            ->selectRaw('`model_id`, SUM(`sub`.`score`) AS `score`')
            ->orderByDesc('score');

        $model = $builder->getModel();

        return $builder->getModel()->newQuery()
            ->rightJoinSub($combinedQuery, 'combined', 'combined.model_id', '=', $model->getTable() . '.' . $model->getKeyName())
            ->orderByDesc('score');
    }

    /**
     * Builds the part of the query that represents bit similarity
     *
     * @param array $query
     * @return string
     */
    private function bitSimilarityQuery(Collection $query): string
    {
        $similarity = $query
            ->map(fn($bit, $i) => 'BIT_COUNT(`bit_' . (int) $i . '` ^ ' . $bit . ')')
            ->join('+');

        return '0.5 - ((' . $similarity . ') / ' . ($query->count() * 64) . ')';
    }

    /**
     * Builders a similarity query for internal items.
     *
     * @param int $take
     * @param string $table1
     * @param string $table2
     * @return string
     */
    private function bitSimilarColumns(int $take, string $table1, string $table2 = 'lsh_search_index'): string
    {
        $similarity = [];
        for($i = 0; $i < $take; $i++) {
            $similarity[] = 'BIT_COUNT(`' . $table1 . '`.`bit_' . $i . '` ^ `' . $table2 . '`.`bit_' . $i . '`)';
        }

        return '0.5 - ((' . implode(' + ', $similarity) . ') / ' . ($take*32) . ')';
    }

    private function weightingQuery(array $weights, string $table = null): string
    {
        if(!empty($weights)) {
            $weighting = 'CASE ' . ( !is_null($table) ? '`' . $table . '`.' : '' ) . '`field`';
            foreach ($weights as $type => $weight) {
                $weighting .= ' WHEN \'' . addslashes($type) . '\' THEN ' . (float) $weight;
            }
            $weighting .= ' END';
            return $weighting;
        }
        return '1';
    }

}
