<?php

namespace SiteOrigin\ScoutLSH\Models;

use Laravel\Scout\Builder;

interface FieldWeights
{
    public function getTypeWeights(Builder $builder = null): array;
}
