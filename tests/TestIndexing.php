<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class TestIndexing extends TestCase
{
    use RefreshDatabase;

    public function test_indexing()
    {
        $toReturn = collect(range(0, 5))
            ->map(function(){
                return [
                    'question' => array_map(fn() => rand(0, 255), range(0, 63)),
                    'answer' => array_map(fn() => rand(0, 255), range(0, 63)),
                ];
            })->toArray();

        TextEncoder::shouldReceive('encode')
            ->times(6)
            ->andReturn(...$toReturn);

        Question::create([
            'question' => 'What are your opening hours?',
            'answer' => 'Monday to Friday: 9am to 5pm',
        ]);
        Question::create([
            'question' => 'What is your address?',
            'answer' => '123 Main St, Anytown, CA 12345',
        ]);
        Question::create([
            'question' => 'Do you have stock of ferrets?',
            'answer' => 'We currently have 18 ferrets in stock, ready for purchase.',
        ]);
        Question::create([
            'question' => 'Do you stock guinea pigs?',
            'answer' => 'Unfortunately, we do not stock guinea pigs because they are too fat.',
        ]);
        Question::create([
            'question' => 'What can guinea pigs eat?',
            'answer' => 'They can eat lots of things like strawberries, hay, and guinea pig pellets.',
        ]);
        Question::create([
            'question' => 'Can you eat guinea pigs?',
            'answer' => "They do eat guinea pigs in some countries but we don't like eating them.",
        ]);

        // Check that the table lsh-search-index has entries for all the questions
        $this->assertEquals(12, DB::table('lsh-search-index')->count());

        TextEncoder::shouldReceive('encode')
            ->once()
            ->andReturn([0 => array_map(fn() => rand(0, 255), range(0, 63))]);

        // Perform a search query and make sure we get some results
        $results = Question::search('Can guinea pigs eat strawberries?')->get();
        $this->assertEquals(6, $results->count());
    }
}
