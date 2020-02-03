<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Book
 *
 * @property int $id
 * @property string|null $isbn
 * @property string $title
 * @property string $description
 * @property string $author
 * @property Carbon $publication_date
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Book newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Book newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Book query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Review[] $reviews
 * @property-read int|null $reviews_count
 */
class Book extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'isbn',
        'title',
        'description',
        'author',
        'publication_date',
    ];

    protected $dates = [
        'publication_date'
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopePublishedBefore(Builder $query, $date): Builder
    {
        return $query->where('publication_date', '<=', Carbon::parse($date));
    }

    public function scopePublishedAfter(Builder $query, $date): Builder
    {
        return $query->where('publication_date', '>=', Carbon::parse($date));
    }
}