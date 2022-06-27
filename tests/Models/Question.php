<?php

namespace SiteOrigin\ScoutLSH\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use SiteOrigin\ScoutLSH\HashSearchable;

class Question extends Model
{
    use Searchable, HasFactory;

    protected $table = 'questions';
    protected $guarded = [];
    protected $fillable = ['question', 'answer', 'category'];

    public function toSearchableArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }

    public function getTypeWeights(Builder $builder): array
    {
        return [
            'question' => 1.0,
            'answer' => 0.5,
        ];
    }
}
