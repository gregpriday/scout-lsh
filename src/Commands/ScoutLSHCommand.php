<?php

namespace SiteOrigin\ScoutLSH\Commands;

use Illuminate\Console\Command;

class ScoutLSHCommand extends Command
{
    public $signature = 'laravel-scout-lsh';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
