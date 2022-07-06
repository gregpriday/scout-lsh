<?php

namespace SiteOrigin\ScoutLSH\Facades;

use Illuminate\Support\Facades\Facade;

class AutoLinker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SiteOrigin\ScoutLSH\Services\AutoLinker::class;
    }
}
