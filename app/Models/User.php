<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'birthday',
        'password',
        'admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function favourites(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'favourites', 'user_id', 'book_isbn');
    }

    public function finishedBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'finished_books', 'user_id', 'book_isbn');
    }

    public function onRead(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'books_in_progress', 'user_id', 'book_isbn');
    }

}
