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

    const BONUS_PERCENT = 3;
    const BONUS_WELCOME = 200;

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

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // adds welcome bonuses to new user
            $user->bonus = self::BONUS_WELCOME;
        });
    }

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

    /**
     * Calculates user's bonus
     * @param int $price
     * @return void
     */
    public function saveBonus(int $price): void
    {
        $bonus = floor(($price / 100) * self::BONUS_PERCENT);
        $this->bonus = $this->bonus + $bonus;
    }

    public function favourites(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'favourites', 'user_id', 'book_isbn');
    }

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'purchases', 'user_id', 'book_isbn');
    }

    public function cart(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'carts', 'user_id', 'book_isbn');
    }

}
