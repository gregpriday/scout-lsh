<?php

namespace SiteOrigin\ScoutLSH\Facades;

use Illuminate\Support\Facades\Facade;

class LSHSearcher extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SiteOrigin\ScoutLSH\Services\LSHSearcher::class;
    }
}
