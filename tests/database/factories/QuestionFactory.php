<?php

namespace SiteOrigin\ScoutLSH\Tests\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SiteOrigin\ScoutLSH\Tests\Models\Question;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public static array $categories = ['General', 'Technology', 'Science', 'History', 'Geography', 'Art', 'Literature'];

    public function definition()
    {
        return [
            'question' => $this->faker->sentence,
            'answer' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(self::$categories),
        ];
    }
}
