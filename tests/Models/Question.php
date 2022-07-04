<?php

namespace SiteOrigin\ScoutLSH\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use SiteOrigin\ScoutLSH\Models\FieldWeights;
use SiteOrigin\ScoutLSH\Models\HasSimilar;

class Question extends Model implements FieldWeights
{
    use Searchable;
    use HasFactory;
    use HasSimilar;

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

    public function getSearchWeights(Builder $builder = null): array
    {
        return [
            'question' => 1.0,
            'answer' => 0.75,
        ];
    }

    public function getSimilarWeights(Builder $builder = null): array
    {
        return [
            'question' => 1.0,
            'answer' => 0.5,
        ];
    }
}
