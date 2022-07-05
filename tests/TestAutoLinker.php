<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;
use SiteOrigin\ScoutLSH\Services\AutoLinker;
use SiteOrigin\ScoutLSH\Services\LSHSearcher;
use SiteOrigin\ScoutLSH\Tests\database\Seeders\RealEstateSeeder;
use SiteOrigin\ScoutLSH\Tests\Models\Listing;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class TestAutoLinker extends TestCase
{
    use RefreshDatabase;

    public function test_auto_linking()
    {
        Question::create([
            'question' => 'Are married people happy?',
            'answer' => 'Married people can be slightly happier than their unmarried counterparts.',
        ]);

        Question::create([
            'question' => 'Will drugs make you happy?',
            'answer' => 'No, unless they are prescribed by a medical professional.',
        ]);

        Question::create([
            'question' => 'Can you make a lot of money from Computer Science?',
            'answer' => 'Yes, you can earn a lot of money in the computer science field.',
        ]);

        Question::create([
            'question' => 'What is the meaning of life?',
            'answer' => 'The meaning of life is purpose and relationships.',
        ]);

        Question::create([
            'question' => 'How can you be happy?',
            'answer' => 'You can be happy by doing things',
        ]);

        Question::create([
            'question' => 'How do you find a purpose in life?',
            'answer' => 'You find your passion, master your passion, then do it a lot.',
        ]);

        Question::create([
            'question' => 'Is there a shortcut to success?',
            'answer' => 'There is no shortcut to success',
        ]);

        $question = Question::create([
            'question' => 'Is computer science a good degree?',
            'answer' => 'Yes, it is a good degree',
        ]);

        Question::create([
            'question' => 'Which is the best degree?',
            'answer' => 'Personally I recommend a degree in a STEM field, but you can choose any field.',
        ]);

        $autoLinker = app(AutoLinker::class);
        $html = 'If you are lacking <a href="#search.question">purpose in your life</a> then you could consider getting a <a href="#search">computer science degree</a> or perhaps a pet <a href="#search">guinea pig</a>.';
        $html = $autoLinker->autolink($html, [
            0.65, [Question::class], ['question' => 1.0, 'answer' => 0.5],
        ]);
        dd($html);

    }
}
