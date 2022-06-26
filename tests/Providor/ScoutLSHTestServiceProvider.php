<?php

namespace SiteOrigin\ScoutLSH\Tests\Providor;

use Illuminate\Support\ServiceProvider;

class ScoutLSHTestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations/create_questions_table.php.stub');
    }
}
