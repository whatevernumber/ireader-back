<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Book extends Model
{
    use HasFactory;

    protected $primaryKey = 'isbn';
    public $incrementing = false;

    public $fillable = [
        'isbn',
        'title',
        'description',
        'price',
        'fragment',
        'has_x',
        'published_year',
        'pages',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(Image::class);
    }

    public function savedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourites', 'book_isbn', 'user_id');
    }

    public function finishedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'finished_books', 'book_isbn', 'user_id')
            ->withPivot('comment', 'rate', 'completed_days', 'created_at')->withTimestamps();
    }

    public function addedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'books_in_progress', 'book_isbn', 'user_id');
    }
}
