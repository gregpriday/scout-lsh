<?php

namespace SiteOrigin\ScoutLSH\Facades;

use Illuminate\Support\Facades\Facade;

class TextEncoder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SiteOrigin\ScoutLSH\Services\TextEncoder::class;
    }
}
