<?php

namespace SiteOrigin\ScoutLSH\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

class Listing extends Model
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

    public function getTypeWeights(Builder $builder): array
    {
        return [
            'title' => 1,
            'description' => 0.8,
        ];
    }
}
