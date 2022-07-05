<?php

namespace SiteOrigin\ScoutLSH\Models;

use Laravel\Scout\Builder;

interface FieldWeights
{
    public function getSearchWeights(Builder $builder = null): array;

    public function getSimilarWeights(Builder $builder = null): array;
}
