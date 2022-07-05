<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class TestFindSimilar extends TestCase
{
    use RefreshDatabase;

    public function test_find_similar_questions()
    {
        $question = Question::create([
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

        $similar = $question->similar()->limit(2)->get()->pluck('question');

        $this->assertEquals('Can you make a lot of money from Computer Science?', $similar[0]);
        $this->assertEquals('Which is the best degree?', $similar[1]);
    }
}
