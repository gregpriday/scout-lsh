<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Services\LSHSearcher;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class TestLSHSearcher extends TestCase
{
    use RefreshDatabase;

    public function test_progressive_searching()
    {
        $questionCount = 100;

        $toReturn = collect(range(0, $questionCount - 1))
            ->map(fn () => [
                'question' => $this->randomHashArray(),
                'answer' => $this->randomHashArray(),
            ]);

        TextEncoder::shouldReceive('encode')
            ->times($questionCount)
            ->andReturn(...$toReturn->toArray());

        Question::factory()->count($questionCount)->create();

        /** @var LSHSearcher $lshSearcher */
        $searcher = app(LSHSearcher::class);

        TextEncoder::shouldReceive('encode')
            ->once()
            ->andReturn([0 => $this->randomHashArray()]);

        $query = $searcher->searchByQuery('random search query', Question::query(), 250)->limit(10);

        $start = microtime(true);
        $results = $query->get();
        $end = microtime(true);
    }
}
