<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Tests\database\Seeders\RealEstateSeeder;
use SiteOrigin\ScoutLSH\Tests\Models\Listing;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class TestIndexing extends TestCase
{
    use RefreshDatabase;

    public function test_indexing()
    {
        $this->refreshDatabase();

        $questionCount = 50;

        $toReturn = collect(range(0, $questionCount-1))
            ->map(fn() => [
                'question' => array_map(fn() => rand(0, 255), range(0, 63)),
                'answer' => array_map(fn() => rand(0, 255), range(0, 63)),
            ]);

        TextEncoder::shouldReceive('encode')
            ->times($questionCount)
            ->andReturn(...$toReturn->toArray());

        $questions = Question::factory()->count($questionCount)->create();

        // Check that the table lsh-search-index has entries for all the questions
        $this->assertEquals($questionCount*2, DB::table('lsh-search-index')->count());

        TextEncoder::shouldReceive('encode')
            ->twice()
            ->andReturn([0 => array_map(fn() => rand(0, 255), range(0, 63))]);

        // Perform a search query and make sure we get some results
        $results = Question::search('Can guinea pigs eat strawberries?')->get();
        $this->assertCount($questionCount, $results);

        $results = Question::search('Can guinea pigs eat strawberries?')->within(Question::where('category', 'Technology'))->get();
        $categories = $results->pluck('category')->unique();

        $this->assertCount(1, $categories);
        $this->assertEquals('Technology', $categories->first());
    }

    public function test_searching_real_estate()
    {
        $this->seed(RealEstateSeeder::class);
        $results = Listing::search("home in chapmans bay estate")->get();
        $results->pluck('title')->dd();
    }
}
