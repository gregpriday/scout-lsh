<?php

namespace SiteOrigin\ScoutLSH\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use SiteOrigin\ScoutLSH\HashSearchable;

class Question extends Model
{
    use Searchable;

    protected $table = 'questions';
    protected $guarded = [];
    protected $fillable = ['question', 'answer'];

    public function toSearchableArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }

    public function getTypeWeights(): array
    {
        return [
            'question' => 1.0,
            'answer' => 0.5,
        ];
    }
}
