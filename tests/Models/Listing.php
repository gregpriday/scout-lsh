<?php

namespace SiteOrigin\ScoutLSH\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use SiteOrigin\ScoutLSH\Models\FieldWeights;

class Listing extends Model implements FieldWeights
{
    use Searchable;

    protected $table = 'listings';
    protected $guarded = [];
    protected $fillable = ['title', 'description', 'area'];

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    public function getSearchWeights(Builder $builder = null): array
    {
        return [
            'title' => 1,
            'description' => 1,
        ];
    }
}
