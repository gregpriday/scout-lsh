<?php

namespace SiteOrigin\ScoutLSH\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Tests\database\Seeders\RealEstateSeeder;
use SiteOrigin\ScoutLSH\Tests\Models\Listing;
use SiteOrigin\ScoutLSH\Tests\Models\Question;
use SiteOrigin\ScoutLSH\Tests\TestCase;

class TestIndexing extends TestCase
{
    use RefreshDatabase;

    public function test_indexing()
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

        $questions = Question::factory()->count($questionCount)->create();

        // Check that the table lsh_search_index has entries for all the questions
        $this->assertEquals($questionCount * 2, DB::table('lsh_search_index')->count());

        TextEncoder::shouldReceive('encode')
            ->twice()
            ->andReturn([0 => $this->randomHashArray()]);

        // Perform a search query and make sure we get some results
        $results = Question::search('random search query')->get();
        $this->assertCount($questionCount, $results);

        $results = Question::search('another search query')->within(Question::where('category', 'Technology'))->get();
        $categories = $results->pluck('category')->unique();

        $this->assertCount(1, $categories);
        $this->assertEquals('Technology', $categories->first());
    }

    public function test_searching_with_field_weights()
    {
        $this->seed(RealEstateSeeder::class);

        // The default search should not get this correct
        $fill = Listing::search("land")->get()->pluck('title')->toArray();
        $weighted = Listing::search("land")->withFieldWeights(['title' => 1])->get()->pluck('title')->toArray();

        $this->assertNotEquals($fill, $weighted);
    }

    public function test_question_answering()
    {
        Question::create([
            'question' => 'Store Business Hours',
            'answer' => 'Our store is open from 9am to 5pm.',
        ]);

        Question::create([
            'question' => 'Store Address',
            'answer' => 'Our store is located at 123 Main St.',
        ]);

        Question::create([
            'question' => 'Services',
            'answer' => 'We offer some really fantastic services.',
        ]);

        Question::create([
            'question' => 'About Us',
            'answer' => 'We are a company that sells stuff.',
        ]);

        Question::create([
            'question' => 'Contact Us',
            'answer' => 'If you would like to contact us, please call us at 555-555-5555.',
        ]);

        Question::create([
            'question' => 'Bookkeeping Service',
            'answer' => 'We offer bookkeeping and accounting services to keep your business accounting up to date.',
        ]);

        Question::create([
            'question' => 'Terms of Service',
            'answer' => 'Terms of service (also known as terms of use and terms and conditions, commonly abbreviated as TOS or ToS, ToU or T&C) are the legal agreements between a service provider and a person who wants to use that service. The person must agree to abide by the terms of service in order to use the offered service.',
        ]);

        $question = Question::search('what are your contact details?')->get()->first();
        $this->assertEquals('Contact Us', $question->question);

        $question = Question::search('can you do my accounting?')->get()->first();
        $this->assertEquals('Bookkeeping Service', $question->question);
    }
}
