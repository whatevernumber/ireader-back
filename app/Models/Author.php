<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
    ];

    protected $hidden = [
        'pivot',
    ];

    public $timestamps = false;

    /**
     * Sorts Authors by name
     */
    public function scopeOrderedByName(Builder $query)
    {
        $query->orderBy('name');
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
